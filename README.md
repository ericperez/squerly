Squerly
=======

Squerly (a portmanteau of 'SQL' and 'Query') is a light-weight, unified business intelligence reporting and data visualization framework written in JavaScript and PHP. What does that mean? Just like application frameworks like Laravel, Spring, Express.js, etc. attempt to build [generic tools](http://fewagainstmany.com/blog/frameworks-dont-have-to-do-everything-and-more) that all (Web) applications need or share in common (80% of the code) and let the developer focus on the actual issues specific to your application (the other 20%,) Squerly attempts to do the same thing with BI reports--it takes care of the most common 80% of what reports need to do and allow the report developer to focus on the 20% that is specific to their needs. Writing, organizing, and publishing Reports such as business intelligence, system monitoring, exception, etc., and presenting the results in tabular and/or graphical form is what Squerly does best--all using a simple RESTful API.

Squerly in in the same vein of software as Tableau or Domo but is not nearly as fully-featured--it is 100% free and open-source software though. It's also 100% RESTful/Web-based so anyone with a Web browser can use it (no native clients required!). It's main purpose is to allow people with a knowledge of SQL to 'save' their queries to a database and and 'share' them (by providing a URL to the report) with end-users who would like to run those queries/reports. It then displays a UI which allows the end user to supply inputs to said queries/reports and get the output back in many different formats (JSON, CSV, graphs, etc.)

In Squerly, all the SCRUD-related code for models lives in a base model (CRUD model) and a base controller (CRUD controller) that all other models and controllers, respectively, extend. The CRUD model and CRUD controller have been designed in such a way that they can be used with any data model which means that Squerly can be extended easily because you never has to worry about writing more code for adding, editing, deleting, viewing, and searching any new data models that are desired (see 'Features' below.)

Note: Squerly should be considered 'beta' software at this time and is not suitable for customer-facing environments (no permissions are built in at this point.) [sovrn](http://www.sovrn.com) has been using Squerly internally for production BI reports (against MySQL) for about three years now and no major issues being reported. If you try Squerly out and you get stuck feel free to shoot me an email: eperez[@]squerly.net or a write up a Github issue and I'll be happy to answer any questions you have about the software.


#Notice

This project has not been updated in a while. I wrote it as a proof-of-concept and it does some pretty cool things even though it lacks some UI polish and (sadly) does not have any unit tests written for it. In the future I might try to do a complete re-write in Node.js but as far as this version is concerned it's unlikely that I will get to any of the TODO items (below) or write unit tests for it. I noticed that under the GitHub code stats that it lists 70%+ of the code as being JavaScript (mostly libraries) so I guess that bodes well for a Node re-write. Code provided AS-IS, but it is open-source so hack away if you feel like it. :)


#More Information
Please see the [WIKI](https://github.com/ericperez/squerly/wiki) for more information about this application (background, system requirements, installation instructions, etc.)


#Features

-  One of the most powerful (but mostly "under the covers") feature that Squerly has is it's incredibly powerful genericized SCRUD functionality including in controllers, models, views, forms, and routes that give you a complete set of SCRUD features 'for free,' based on the underlying data structure of your models. This is one feature that I wish I had built out separately as it's own independent component. All of the data for models in Squerly are stored in a SQLite database (but they could live in SQL Server, MySQL, etc.) If you add a new table to /data/squerly.sqlite and add it to the "CRUD model whitelist" ($model_whitelist) in /helpers/crud_helper.php you will automatically get all the things listed above (such as routes, CRUD forms, etc.) without having to write any additional code. This makes extending the application with more data model/entities incredibly fast and painless because the only thing you have to do is think about what properties/fields your new model/entity will have, write whatever custom logic it requires, and the built-in SCRUD system takes care of (pretty much) everything else for you.

-  Squerly lets you load data from many sources (CSV files, XML files, JSON files, SQL databases [MySQL, PostgreSQL, SQLite, MSSQL], RESTful APIs, etc.) It's also extensible so that more data sources can be added easily.

-  You can also run any PHP preprocessing code necessary to get the source data into the format that you need it to be in (aggregations, column elimination, counting, etc.)

-  Output from Squerly reports can be presented to the end-user in a wide variety of formats, such as an HTML Table, CSV, JSON, XML, Graphs/Charts, etc.

-  Report input parameters can be introduced by using simple template tags (in the format "{[tag]}") in the report query. (This is something that even Tableau and Domo, etc. cannot do easily and what I consider to be a "killer feature.") By simply placing a ([mustache](https://mustache.github.io/)-based) macro inside of your report queries, Squerly will 1.) Generate an HTML form for the end-user that includes a (named and labeled) form field for each macro, 2.) Once the 'run report' form is submitted to Squerly, it will place the inputs sent to it to the data store (such as MySQL) as a 'bind parameter', and send the query to the database as a prepared statement.

-  Reports are easy to write and easy to deploy because they are all built in a unified fashion (as objects, not code!) so they can be easily serialized/stored/migrated.

# TODO

-  Scheduled Events--want to get the output of a report in your inbox on a recurring basis? Scheduled events will allow you to do that

-  Input Validation--Validation for report input parameters still needs to be implemented

-  Data transformations--Aggregates, slicing & dicing of the data in various ways

-  More data source options and output formats (I'm currently working on Cassandra CQL and Apache Hive [over SSH] data source loaders)

-  Better control over the caching of report results

-  Event/Application logging

-  User Accounts and Authentication

-  Dashboards (allowing for multiple result sets to be presented on one screen, or rotate in a slideshow-type fashion)

-  Spruce up the graphical interface for the administration/CRUD pages (I know it's pretty ugly right now; I'm no designer obviously.)


#License

Squerly is distributed under the [GPL v3](http://www.gnu.org/licenses/gpl.html) or later license.
