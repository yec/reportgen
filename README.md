Reportgen
=========

We use this utility to quickly generate a html report framework
given contents and template files.

Usage
-----

Within the reportgen folder run:

    ./generate.php sample_report

This will output the sample report html files to sample_report/output

### Modify template.html

You probably need to modify the template of the page. This is done
by modifying sample_report/root/template.html

### Modify pages.txt

pages.txt is where reportgen finds the pages to generate. It is in the
form of a list and can handle 2 levels of menu.

title | filename (no extension) \[ | pdf \] (optional if link is pdf)
