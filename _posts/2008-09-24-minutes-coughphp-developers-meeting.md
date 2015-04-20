---
title: Minutes CoughPHP Developers Meeting
---

Austin, TX, September 23, 2008.

Present
-------

-   Anthony Bush
-   Scott Hudnall
-   Richard Pistole
-   Tom Warmbrodt
-   Lewis Zhang

Subject
-------

### Generated `remove*()` methods.

#### Issue

Currently the generated `remove*()` methods for generated classes that extend `CoughObject` do not behave in an intuitive way. They NULL out the related key, orphaning the data but not deleting it.

#### Discussion

We discussed removing the methods all together or switching to some acceptable behavior:

1.  Delete the data.
2.  “Retire” the data (e.g. toggle a flag or status column).
3.  Negate the ID (i.e. instead of NULLing it as is done now).

#### Resolution

We provide a deletion strategy option, settable through config. At minimum, we will provide (a) and (b) strategies already implemented out of box.

### Many-to-many handling

#### Issue

Currently there is no explicit many-to-many handling. The methods must be written manually, or one must go through the generated join methods (think one-to-many-to-one).

#### Discussion

We talked about some of the reasons we moved away from the old CoughPHP behavior where these were once auto-detected and generated.

-   It was possible to run into conflicting method names (anytime two tables were joined by more than one join table) and there was no way to resolve this other than to manually modify the generated file.
-   Join tables didn’t get generated classes at all, which meant we had to invent a new API for getting and setting that data in the event there were other columns besides just the two ID columns making up the join.

#### Resolution

We revive the behavior with these restrictions:

-   We continue to generate the one-to-many methods straight to the join table. These will be the preferred way of getting extra join data should it be needed. In other words, we will not revive the old way of getting and setting join data.
-   We auto-detect, possibly using a many-to-many detection strategy so you can override the `table_one2table_two` naming convention (if using foreign keys, naming convention may not be required).
-   While auto-detecting, if there is a conflict (more than one path to join two tables), we stop generating for that table’s classes and throw a warning for that table with instructions on how to specify which path is the correct one.
-   We provide config option that allows specifying of correct path to join tables (required to satisfy above warning message requirement).
-   We provide config option to turn off auto-detection all together. In this scenario, the only many-to-many generation would come from the above config option where items are explicitly specified.

### `CoughObject::delete()`

#### Issue

Currently CoughObject provides a public `delete()` method which falls outside of the “set, set, set, save” behavior because deletion happens immediately.

#### Discussion

We considered making a call to `save()` required after calling `delete()`. We did not like that because `delete()` flows with the already existing `insert()` and `update()` methods which also take effect immediately (those two methods are protected and `save()` decides which one to call).

#### Resolution

We introduce a new method called `remove()` which will mark the object for deletion (based on the deletion strategy in use). A call to `save()` will be required to make the delete effective. This name was chosen because it follows with the other add/remove methods which also require a `save()` call to take affect. We want to move `delete()` to a protected method like `insert()` and `update()` but will leave it public for one release. This will give users a chance to move their code over to the new way before it becomes protected.

### Release and packaging

#### Issue

With the resolution for “Generated remove\*() methods” we could not agree that CoughPHP should default in enterprise/safe mode (the flag/status column deletion strategy).

#### Discussion

We considered defaulting to “delete” behavior as many agreed that is the expected behavior to someone new to CoughPHP. But, we are also trying to push better practices in addition to preventing users from accidentally or unknowing deleting their (or their company’s) data.

#### Resolution

We provide a config option that must be set before the generator will continue. An message explaining how to set this will be given if the generator is run without this option set.
