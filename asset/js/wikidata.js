(function () {
    // Tooltips are delegated from a single global selector, so this must be
    // registered once for the page rather than once per value: Tipped stores a
    // new delegation on every call and never de-duplicates them.
    var tippedDelegated = false;

    function delegateTooltips() {
        if (tippedDelegated) {
            return;
        }
        tippedDelegated = true;
        Tipped.delegate('.autocomplete-suggestion', {
            skin: 'light',
            size: 'large',
            radius: false,
            position: 'topleft'
        });
    }

    $(document).on('o:prepare-value', function (e, type, value) {
        if (0 !== type.indexOf('wikidata:')) {
            return;
        }

        var thisValue = $(value);
        var suggestInput = thisValue.find('.wikidata-input');
        var labelInput = thisValue.find('input[data-value-key="o:label"]');
        var idInput = thisValue.find('input[data-value-key="@id"]');
        var valueInput = thisValue.find('input[data-value-key="@value"]');
        var languageInput = thisValue.find('input[data-value-key="@language"]');
        var idContainer = thisValue.find('.wikidata-id-container');

        function showUri(uri, label) {
            idInput.val(uri).prop('disabled', false);
            labelInput.val(label).prop('disabled', false);
            valueInput.prop('disabled', true);
            idContainer.show().find('.wikidata-id').html(
                $('<a>').attr('href', uri).attr('target', '_blank').attr('rel', 'noopener').text(uri)
            );
        }

        function showLiteral(text) {
            idInput.val('').prop('disabled', true);
            labelInput.val('').prop('disabled', true);
            valueInput.val(text).prop('disabled', false);
            idContainer.hide();
        }

        // Literal is the default type.
        idInput.prop('disabled', true);
        labelInput.prop('disabled', true);
        valueInput.prop('disabled', false);
        idContainer.hide();

        // Set existing values during initial load.
        if (idInput.val()) {
            suggestInput.val(labelInput.val()).attr('placeholder', labelInput.val());
            showUri(idInput.val(), labelInput.val());
        } else if (valueInput.val()) {
            suggestInput.val(valueInput.val()).attr('placeholder', valueInput.val());
        }

        // Synchronize the suggest input with o:label or @value.
        suggestInput.on('input', function () {
            if (idInput.val()) {
                labelInput.val($(this).val());
            } else {
                valueInput.val($(this).val());
            }
        });

        // Results are language-specific, so a language change invalidates them.
        // The language control itself is core's: this markup is core's
        // .language-wrapper shape, and core already delegates the toggle, the
        // datalist and validation from #resource-values.
        languageInput.on('input', function () {
            suggestInput.autocomplete().clearCache();
        });

        // Drop the @id and let the value behave as a literal again.
        idContainer.find('.wikidata-id-remove').on('click', function (e) {
            e.preventDefault();
            showLiteral(labelInput.val());
        });

        suggestInput.autocomplete({
            serviceUrl: wikidataProxyUrl,
            deferRequestBy: 200,
            minChars: 3,
            // Must disable preventBadQueries or autocomplete will not fire on
            // queries that share a root that previously returned no results.
            preventBadQueries: false,
            // Must disable triggerSelectOnValidInput or onSelect will be
            // triggered whether the user wants it or not. The user must
            // explicitly select the suggestion.
            triggerSelectOnValidInput: false,
            // Set lang in onSearchStart so the query always uses the language
            // currently in the field. Set type here as well for consistency.
            onSearchStart: function (params) {
                $(this).css('cursor', 'progress');
                params.lang = languageInput.val();
                params.type = type;
            },
            onSearchComplete: function () {
                $(this).css('cursor', 'default');
            },
            onSearchError: function (query, jqXHR) {
                $(this).css('cursor', 'default');
                // A failed lookup and "no matches" look identical in the field,
                // which is why this has been reported as the module simply not
                // working. Leave a trace an administrator can actually find.
                var detail = (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.error)
                    ? jqXHR.responseJSON.error
                    : ((jqXHR && jqXHR.status) ? 'HTTP ' + jqXHR.status : 'unknown error');
                console.warn('Wikidata suggest failed for "' + query + '": ' + detail);
            },
            beforeRender: function (container, suggestions) {
                container.children().each(function (index) {
                    var suggestion = suggestions[index];
                    if (!suggestion || !suggestion.data) {
                        return;
                    }
                    $(this).attr('title', suggestion.data.info);
                    if (suggestion.data.preview) {
                        $(this).attr('data-tipped-options', 'ajax: { url: ' + JSON.stringify(suggestion.data.preview) + ' }');
                    }
                });
            },
            // Prepare the value when the user selects a suggestion.
            onSelect: function (suggestion) {
                suggestInput.val(suggestion.value).attr('placeholder', suggestion.value);
                if (suggestion.data && suggestion.data.uri) {
                    showUri(suggestion.data.uri, suggestion.value);
                } else {
                    showLiteral(suggestion.value);
                }
            }
        });

        delegateTooltips();
    });
})();
