Squerly
=======

Squerly (pronounced 'squirrely') is a light-weight, unified reporting framework written in PHP. What does that mean? Just like PHP application frameworks like Zend Framework, Symfony, etc. attempt to build [generic tools](http://fewagainstmany.com/blog/frameworks-dont-have-to-do-everything-and-more) that all Web applications need or share in common (80% of the code) and let the developer focus on the actual issues specific to your application (the other 20%,) Squerly attempts to do the same thing with reports--take care of the most common 80% of what Web Application reports need to do and allow the report developer to focus on the 20% that is specific to their needs.

Note: Squerly is currently under active development and likely contains many bugs! It should be considered 'pre-alpha' software at this time and is not suitable for prodution environments at this time.

See the [Wiki](https://github.com/ericperez/squerly/wiki) for more information (installation, setup, usage, etc.)


#Features

-  Load data from many sources (CSV files, XML files, JSON files, and SQL databases [MySQL, PostgreSQL, SQLite]) It's also extensible so that more data sources can be added easily.

-  Run any PHP preprocessing code necessary to get the source data into the format that you need it to be in (aggregations, column elimination, counting, etc.)

-  Output the report results in a wide variety of formats, such as HTML Table, CSV, JSON, XML, KML (to map your data with Google Maps), etc. A variety of graphing formats will become available soon as well.

-  Report input parameters can be introduced by using a simple templating format in the report query or input URI.

-  Reports are easy to write and easy to deploy because they are all built in a unified fashion (as objects)


#License

Squerly is distributed under the [GPL v3](http://www.gnu.org/licenses/gpl.html) or later license.


