![Wikidata Module for Omeka-S](https://nishad.github.io/omeka-s-wikidata/docs/images/module-banner.png)

An Omeka-S module based on [ValueSuggest](https://omeka.org/s/modules/ValueSuggest/) for auto-suggest Wikidata URIs and labels.

### Autocomplete URIs and Label
This module adds an auto-complete feature to a specific property in a resource template and suggests items from Wikidata and auto-fills the URI with a label in the preferred language. Users will always have the option of creating custom values instead.

![Item property Subject is typed in the field. A drop-down menu auto-suggests items](https://nishad.github.io/omeka-s-wikidata/docs/images/demo.gif)

### Multilingual

Helps to retrieve labels in different languages

![Item property Subject is typed in the field. Language is set to Japanese. A drop-down menu auto-suggests items with Japanese label.](https://nishad.github.io/omeka-s-wikidata/docs/images/demo_ja.gif)

## Requirements

Omeka S 4.0 or later. Version 1.4.2 and earlier supported Omeka S 3.x; see [Omeka S version support](#omeka-s-version-support) below.

## Installation

Uncompress files from release zip or clone repository and rename module folder to `Wikidata`. See general end user documentation for [Installing a module](http://omeka.org/s/docs/user-manual/modules/#installing-modules)

> **The folder must be named exactly `Wikidata`.** Omeka identifies a module by its directory name but loads its classes from a path derived from the module's namespace, so a folder named `wikidata` or `omeka-s-wikidata-master` will either fail to load or appear to install and then error when a resource template is opened.


### Installing from the latest release

Download `zip` or `tar.gz` file from the [latest release](https://github.com/nishad/omeka-s-wikidata/releases/latest) and uncompress it to the `Wikidata` folder inside Omeka's `modules`. All module files should be inside `/your-omeka-path/modules/Wikidata`.


### Installing latest development branch 

Download [latest development version as a zip file](https://github.com/nishad/omeka-s-wikidata/archive/master.zip). Or use the following commands in your Linux/Mac environments.

``` bash
cd /your-omeka-path/modules/
curl -L -o omeka-s-wikidata-master.zip https://github.com/nishad/omeka-s-wikidata/archive/master.zip
unzip omeka-s-wikidata-master.zip
mv omeka-s-wikidata-master Wikidata
rm omeka-s-wikidata-master.zip
```

## Usage

### Enabling Wikidata suggestion for properties

Wikidata URI suggestions are enabled through Resource Templates. For additional information on Resource Templates, see the [Resource Template Documentation](https://omeka.org/s/docs/user-manual/content/resource-template/).

1. From the Resources templates tab in the Admin Dashboard, add a new [template](https://omeka.org/s/docs/user-manual/content/resource-template/) or edit an existing one.
2. Add the property to which you want to apply Wikidata URI suggestion. 
3. Once the property is added to the template, click the pencil/edit icon for that property.
4. At the bottom of the drawer, which opens on the right, open the *Data type* drop-down. Below the standard options, you will see the Wikidata options. Select the type of suggestion you want to use from the drop-down.
    - Note that you can also add alternate labels and comments for the property in this drawer.
6. Click the *Set changes* button at the bottom of the drawer to assign the Values to the property. 
7. Save changes to the resource template. 


![Editing the property place, and the drop-down is open to show the Wikidate suggestion for locations](https://nishad.github.io/omeka-s-wikidata/docs/images/enable-for-properties.gif)

When you click the Resource Template title to see its details, the Wikidata suggestion type will appear under the Data Type table heading.

![A red rectangle highlights the fact that the data type for subject and place](https://nishad.github.io/omeka-s-wikidata/docs/images/resource-template.png)


### Adding Wikidata URI to an Item

When this Resource Template is used in an Item or Item Set, the designated properties will auto-suggest values from the type of Wikidta item specified in the template. 

Users must start typing in the open text box of that specific property to prompt the auto-suggest feature. There may be a slight delay, but a drop-down menu will appear with choices drawn directly from Wikidata.

![Item property Subject is typed in the field. A drop-down menu auto-suggests items](https://nishad.github.io/omeka-s-wikidata/docs/images/demo.gif)

Hover over selections in the drop-down menu for a preview of that Wikidata item.

## Citation

If our work is helpful to you, please kindly cite our paper as:

> Thalhath N., Nagamori M., Sakaguchi T., Sugimoto S. (2021) Wikidata Centric Vocabularies and URIs for Linking Data in Semantic Web Driven Digital Curation. In: Garoufallou E., Ovalle-Perandones MA. (eds) Metadata and Semantic Research. MTSR 2020. Communications in Computer and Information Science, vol 1355. Springer, Cham. https://doi.org/10.1007/978-3-030-71903-6_31


```
@InProceedings{10.1007/978-3-030-71903-6_31,
author="Thalhath, Nishad
and Nagamori, Mitsuharu
and Sakaguchi, Tetsuo
and Sugimoto, Shigeo",
editor="Garoufallou, Emmanouel
and Ovalle-Perandones, Mar{\'i}a-Antonia",
title="Wikidata Centric Vocabularies and URIs for Linking Data in Semantic Web Driven Digital Curation",
booktitle="Metadata and Semantic Research",
year="2021",
publisher="Springer International Publishing",
address="Cham",
pages="336--344"}
```


## Converting existing values

The Wikidata data types are available as targets in batch edit's *Convert data type*, so a property already holding Wikidata URIs as plain `uri` or text values can be promoted in bulk. A value converts only when a Wikidata entity URI can be identified in it, in either the URI or the text field; the URI is normalised to `http://www.wikidata.org/entity/…` and the label is kept. Values that do not name an entity are left exactly as they are.

## Omeka S version support

| Module version | Omeka S |
| --- | --- |
| 1.5.0 and later | 4.0 and later |
| 1.4.2 and earlier | 3.x, and 4.x from 1.4.1 (but see below) |

Support for Omeka S 3.x was dropped in 1.5.0. Omeka S 4 replaced the resource form's language control with different markup, and the two are mutually exclusive: Omeka S 3 requires the language input to be the toggle's immediate sibling, Omeka S 4 requires it to sit inside a `.language-wrapper` ancestor. Targeting both meant carrying two sets of markup and stylesheets, which is what left the language selector broken on every version. The module now uses Omeka S 4's own markup, so the language control behaves exactly like it does for every other data type.

Note that releases up to and including 1.4.2 do not work on Omeka S 4 despite advertising support for it: they call `Laminas\Crypt\Hash` and `Laminas\Cache\StorageFactory`, neither of which ships with Omeka S 4.

## Todo
- [ ] Basic Configuration
- [ ] Cache switching
- [ ] Configuring from Omeka Admin section
- [ ] Value annotation support (`ValueAnnotatingInterface`)
- [ ] Sample SPARQL filter
- [ ] Sample Custom filters 
- [ ] Detailed documentation
- [ ] Move some of the API access to browser

More Wikidata centric features and configuration options, including configurable filters and custom SPARQL queries, are being developed. This module is kept entirely independent from ValueSuggest to support future developments as a dedicated Wikidata addon.

Pull requests are more than welcome!

## License
Usage is provided under the [GNU General Public License version 3](https://opensource.org/licenses/GPL-3.0). See LICENSE for the full details.
