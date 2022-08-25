# SilverStripe Subsites with theme colours 

Allow subsites to generate theme colours for CMS and TinyMCE

## Requirements

toastnz/blocks - output the theme colours as background colours on each block.

## Installation

```
composer require toastnz/subsites-theme
```

Add the following to your `mysite.yml` :

```yaml
SilverStripe\Subsites\Model\Subsite:
  has_subsites_colours: true
```


