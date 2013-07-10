Squerly
=======

Squerly (a portmanteau of 'SQL' and 'QUERY' and pronounced 'squirrely') is a light-weight, unified reporting and data visualization framework written in PHP. What does that mean? Just like PHP application frameworks like Zend Framework, Symfony, etc. attempt to build [generic tools](http://fewagainstmany.com/blog/frameworks-dont-have-to-do-everything-and-more) that all Web applications need or share in common (80% of the code) and let the developer focus on the actual issues specific to your application (the other 20%,) Squerly attempts to do the same thing with reports--take care of the most common 80% of what Web Application reports need to do and allow the report developer to focus on the 20% that is specific to their needs.

Note: Squerly is currently under active development and likely contains many bugs! It should be considered 'pre-alpha' software at this time and is not suitable for prodution environments. Unfortunately, the documentation on the site is also getting out of date. If you're  brave enough to try out the software in it's current state and you get stuck (and you will!) feel free to shoot me an email: eperez[@]squerly.net and I'll be happy to answer any questions you have about the software.


See the [Wiki](https://github.com/ericperez/squerly/wiki) for more information (installation, setup, usage, etc.)


#Features

-  Load data from many sources (CSV files, XML files, JSON files, SQL databases [MySQL, PostgreSQL, SQLite], RESTful APIs, etc.) It's also extensible so that more data sources can be added easily.

-  Run any PHP preprocessing code necessary to get the source data into the format that you need it to be in (aggregations, column elimination, counting, etc.)

-  Output the report results in a wide variety of formats, such as HTML Table, CSV, JSON, XML, KML (to map your data with Google Maps), Graphs/Charts, etc.

-  Report input parameters can be introduced by using a simple templating format in the report query.

-  Reports are easy to write and easy to deploy because they are all built in a unified fashion (as objects) so they can be easily serialized/stored/migrated.


# TODO

-  Saved Report--these will allow you to 'save' snapshots of report input parameters and recall them later

-  Scheduled Events--want to get the output of a report in your input on a recurring schedule? Scheduled events will allow you to do that

-  Input Validation--Input validation for reports still needs to be implemented

-  Data transformations--Aggregates, slicing & dicing of the data in various ways

-  More data source options and output formats (I'm currently working on Cassandra CQL and Apache Hive [over SSH] data source loaders)

-  Better control over the caching of report results

-  Event/Application logging

-  User Accounts and Authentication

-  Spruce up the graphical interface for the administration/CRUD pages (I know it's pretty ugly right now; I'm no designer obviously.)


Big things are in the works. Stay tuned for more details in the coming months...


#License

Squerly is distributed under the [GPL v3](http://www.gnu.org/licenses/gpl.html) or later license.
