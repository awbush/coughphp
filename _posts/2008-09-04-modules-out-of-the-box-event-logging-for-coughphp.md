---
title: 'Modules: Out-of-the-box Event Logging for CoughPHP'
---

The Cough framework enables and encourages a number of cool (but totally optional) modules that make it even more awesome and useful. I'd like to touch on one potential module that fits nicely with ORM and would make many developers very happy: event logging, i.e. preserving the change history of information that is important to a business. There are discussions of how to do this, at great length, in a number of books and tech blogs. The implementation varies. But in general, all meaningful user actions within our business applications should be logged. Here are three specific methods for event logging / history tracking, agnostic to Cough.

Three techniques to preserve the integrity of our data's history
----------------------------------------------------------------

All three have a home within our system. Applying them at the right time and in the right manner is key. Building these three methods into our mental toolkits (as well as standardizing their usage) will help us tremendously going forward.

### Retire & Insert, Don't Update or Delete

The first technique offers 100% protection of the data's history. When a record's data needs to change, that record should not be updated. Instead, the original record should be marked is\_retired and a new record should be inserted. Each record that could have this condition met should store a retired\_datetime (and probably a retired\_user\_id); obviously all records should have a creation\_datetime, and probably a creation\_user\_id as well. Advantages:

-   This gives perfect information about the records and their complete history
-   The data is always available
-   This technique is relatively easily to implement, and is immediately understood by new developers
-   The sanctity of this technique can be maintained by simple sanity checks that immediately alert developers if for some reason the scheme has broken down; these sanity checks afford nearly 100% awareness when the technique is compromised

Liabilities:

-   This technique makes largescale updates to the database more difficult (but not impossible)
-   This can be the least efficient technique for space reasons. If large numbers of records are modified often, this can cause the table to rapidly grow in size. The table grows in linear proportion to the number of records changed
-   For tables with a publicly available unique accessor (product\_id, for instance), this accessor cannot be the primary key
-   Archiving history is laborious; archival processes meant to reduce the size of the master tables must be run against the production database
-   The technique's utilization is not obvious simply by looking at the table

Sanity Checks:

-   All nonretired records should have an identical creation\_datetime and last\_modified\_datetime
-   All retired records should have an identical retired\_datetime and last\_modified\_datetime
-   The count of nonretired records sharing the same publicly available unique accesesor (aka lookup id) must be equal to either 0 or 1

Use for:

-   Essential business data assets that change only infrequently like customer accounts, addresses, phone numbers.

Don't use for:

-   Things that are modified many times over their lifecycle, like vendor product records or order records.

### Transaction Logging

This technique offers varying levels of protection (up to 100%) of the data's history, depending on the scale of the logging that you wish to implement. When a record is altered, its original state and its destination state are logged to a table. At the simplest (and least efficient, as far as size is concerned), each transaction logging table has an autoincrementing key, all of the columns of the table it is logging changes for (preceded with a prefix like "original\_"), and those same columns again (prefixed by "destination\_", for example). (Newly inserted records in the master table should get a log record with NULL original\_ fields.) Finally, columns indicating when the transaction took place (and if meaningful, who initiated the transaction) should be added. Some columns can obviously be removed (last\_modified\_date, for example) from the logging table. Other columns can only arguably be removed. The more columns that are removed, the smaller the footprint of the table and the less accurate this technique. Size is less of a concern, however, since the logging table is independent of the table that it logs. Advantages:

-   May provide perfect information about the records and their complete history, depending on design decisions
-   Data easily archived with little impact on production systems
-   Can be pared down to log only important changes
-   No worries about maintaining a publicly available unique accessor

Liabilities:

-   This technique makes large scale updates to the database more difficult (but not impossible)
-   Falls victim to forgotten upkeep -- if the parent schema changes, developers must remember to update the logging table schema (and code) as well
-   At its fattest, it consumes space extremely quickly, in proportion to the number of changes times twice the average record size
-   Requires design decisions (what to log), thusly exposing data to risk

Sanity Checks:

-   All master records should have a last\_modified\_datetime that matches the last\_modified\_datetime of the most recent logging record; all logged "destination\_" fields of the most recent log record should match the current values of the master record

Use for:

-   Things that change often and in multiple ways simultaneously, like order and order line records.

Don't use for:

-   Business events that aren't tied largely to changes made to a single table

### Event Logging

Where the first two techniques offer history centered around changes to the database, this method is independent of those constraints. It is simultaneously the easiest to utilize, the most flexible, the simplest to maintain, and the riskiest. In this technique, a generic "event" table is created, alongside an "event\_type" table. Each event that is important to the business is logged to this table. These events are not limited to changes in data; any important business event can be logged to this table. However, in the interest of meaningfully logging changes to the database, the event and event\_type tables are equipped to be bound, flexibly, to any record in the system. Example table schemas:

    event_type
    ----------
    event_type_id
    event_type_name
    event_type_description
    database_name [allow null]
    table_name [allow null]
    key_column_name [allow null]
    creation_datetime
    last_modified_datetime
    is_retired

    event
    -----
    event_id (bigint)
    event_type_id
    key_column_id (bigint) [allow null]
    modified_field_name [allow null]
    original_value [allow null] (varchar 65535)
    destination_value [allow null] (varchar 65535)
    event_note [allow null] (varchar 65535)
    creation_datetime
    last_modified_datetime
    is_retired

In this scenario, types of changes to any table worthy of logging are entered in as specific event\_types. When changes are made, event records are inserted of the appropriate event\_type. If a change was made to a particularly important field, that field's name, original value, and destination value are easily recorded (with constraints on logging TEXT, BLOB, etc changes and/or making the data type of the thing that was changed easily visible). Advantages:

-   Extremely flexible and easily reused; easily maintained
-   Can be utilized to log any business event, not just changes made to the database
-   Event tables stand alone and are easily archived autonomously from production systems
-   Centralized event logging approach makes universal controls, changes simple

Liabilities:

-   This technique makes largescale updates to the database extremely difficult (but not impossible)
-   The more universally utilized this technique is, the more rapidly the table grows
-   If multiple field-modifications to a single record must be logged, multiple event records are required at a rate of one per logged field change
-   Requires design decisions (what to log), thusly exposing data to significant risk
-   Data about each event can become difficult to analyze

Use for:

-   Business events that need to be logged (sending email to customers, for instance), logging infrequent-but-important single-field changes

Don't use for:

-   The exclusive method for logging the history of key data assets; logging all changes to records that have multiple values change simultaneously
