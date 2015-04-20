---
title: 'CoughPHP : Collection Handling Framework'
keywords: php orm, lightweight orm
description: CoughPHP is a lightweight ORM that is extremely flexible and easy to use
---

CoughPHP: PHP ORM
=================

Quick Overview
--------------

Cough is an extremely lightweight PHP [ORM](http://en.wikipedia.org/wiki/Object-relational_mapping) framework for dealing with objects that have single table counterparts in a database. Cough is built to be easy to learn, use, and extend.

Cough generates all the code you need for managing the object model-to-relational model mapping. This includes simple methods for all your [CRUD](http://en.wikipedia.org/wiki/Create%2C_read%2C_update_and_delete) functionality. This also includes Cough Collection classes that represent the relationships between tables in your data model.

Unlike [MVC frameworks](http://en.wikipedia.org/wiki/Model-view-controller#PHP) (in PHP and other languages), Cough doesn't control how you handle your views and controllers. In an MVC application, it intends to only be the model, or a portion of the model. Because of this, it is an excellent choice for projects that involve custom development that must integrate with other existing applications.

Cough is an open source software project and is driven by community contributions. It is under the [FreeBSD license](http://en.wikipedia.org/wiki/BSD_license).

CRUD Demo
---------

Create (INSERT)

{% highlight php startinline %}
$customer = new Customer();
$customer->setName('First Customer');
$customer->save();
$customerId = $customer->getKeyId();
{% endhighlight %}

Read/Retrieve (SELECT)

{% highlight php startinline %}
$customer = Customer::constructByKey($customerId);
{% endhighlight %}

Update (UPDATE)

{% highlight php startinline %}
$customer->setName('New Name');
$customer->save();
{% endhighlight %}

Delete/Destroy (DELETE)

{% highlight php startinline %}
$customer->delete();
{% endhighlight %}

Cough Features
--------------

-   ### Code generation

    Cough generates all the code you need to read and write from the database. 80% of all the code your project will need is created with a single click.

-   ### Extensible Architecture

    Cough generates core classes and 'starter classes' that extend them. Your enhancements start there!

-   ### Interoperability

    Because it doesn't try to do too much, Cough can easily integrate with other PHP projects.

-   ### Efficiency

    Cough generates a class-based ORM; it doesn't needlessly perform dynamic schema lookups while running. Cough also allows for easy overriding of its Collections, making performance optimizations a snap.

-   ### Simplicity

    Designed to solve a specific set of problems; easy to learn, use, and enhance!

-   ### Open Source Licensing

    Free to use and community-driven!
