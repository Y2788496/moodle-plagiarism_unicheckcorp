# moodle-plagiarism_unicheck  

[![Build Status](https://travis-ci.org/Unplag/moodle-plagiarism_unplag.svg?branch=master)](https://travis-ci.org/Unplag/moodle-plagiarism_unplag)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Unplag/moodle-plagiarism_unplag/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Unplag/moodle-plagiarism_unplag/?branch=master)

Unicheck Plagiarism plugin for Moodle

**Supported Moodle versions:** 3.3  
**Supported PHP versions:** 5.6 - 7.1  
**Moodle plugins directory:** https://moodle.org/plugins/plagiarism_unplag

Author: Ben Larson <developer@unicheck.com>  
Copyright: UKU Group, LTD, https://www.unicheck.com  

 > Unicheck is a commercial Plagiarism Prevention product owned by UKU Group, LTD - you must have a paid subscription to be able to use this plugin.  

## QUICK INSTALL  

1. Get latest release (zip file) on [GitHub](https://github.com/Unplag/moodle-plagiarism_unplag/releases) or [Moodle plugins directory](https://moodle.org/plugins/plagiarism_unplag)
2. Follow the instructions described [here](https://docs.moodle.org/31/en/Installing_plugins#Installing_via_uploaded_ZIP_file) to install plugin
3. Enable the Plagiarism API under admin > Advanced Features  
4. Configure the Unicheck plugin under admin > plugins > Plagiarism > Unicheck  

## Dependencies  

1. For supporting RAR archives you have to install php-ext using command bellow 
```sh
pecl install rar
```

## Changelog

| Version | Date | Changelog |
| ------- | ---- | --------- |
| 2.1.0 | Sept 21, 2017 | * Added support RAR files |