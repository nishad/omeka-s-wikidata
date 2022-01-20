![Wikidata Module for Omeka-S](https://nishad.github.io/omeka-s-wikidata/docs/images/module-banner.png)

An Omeka-S module based on [ValueSuggest](https://omeka.org/s/modules/ValueSuggest/) for auto-suggest Wikidata URIs and labels.

### Autocomplete URIs and Label
This module adds an auto-complete feature to a specific property in a resource template and suggests items from Wikidata and auto-fills the URI with a label in the preferred language. Users will always have the option of creating custom values instead.

![Item property Subject is typed in the field. A drop-down menu auto-suggests items](https://nishad.github.io/omeka-s-wikidata/docs/images/demo.gif)

### Multilingual

Helps to retrieve labels in different languages

![Item property Subject is typed in the field. Language is set to Japanese. A drop-down menu auto-suggests items with Japanese label.](https://nishad.github.io/omeka-s-wikidata/docs/images/demo_ja.gif)

## Installation

Uncompress files from release zip or clone repository and rename module folder to `Wikidata`. See general end user documentation for [Installing a module](http://omeka.org/s/docs/user-manual/modules/#installing-modules)


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


## Todo
- [ ] Basic Configuration
- [ ] Cache switching
- [ ] Configuring from Omeka Admin section
- [ ] Sample SPARQL filter
- [ ] Sample Custom filters 
- [ ] Detailed documentation
- [ ] Move some of the API access to browser

More Wikidata centric features and configuration options, including configurable filters and custom SPARQL queries, are being developed. This module is kept entirely independent from ValueSuggest to support future developments as a dedicated Wikidata addon.

Pull requests are more than welcome!

## License
Usage is provided under the [GNU General Public License version 3](https://opensource.org/licenses/GPL-3.0). See LICENSE for the full details.
