---
title: Cough Data Validation - CoughPHP
---

Cough Data Validation
=====================

Cough will refuse to save your object if there are validation errors. By default, no data is invalidated. There are two ways to (in)validate data.

1. Inside the model
2. Outside the model

Validation Inside the Model
---------------------------

To validate data inside the model, add a validation function to the sub class named `doValidateData()` that accepts an array of data matching `[db_column_name] => [value]` and makes calls to `invalidateField()`. For example:

	protected function doValidateData(&$data)
	{
		if (empty($data['first_name'])) {
			$this->invalidateField('first_name', 'First Name is required.');
		}
		if (empty($data['last_name'])) {
			$this->invalidateField('last_name', 'Last Name is required.');
		}
		if (empty($data['email'])) {
			$this->invalidateField('email', 'E-mail is required.');
		} else if (!Validator::isValidEmail($data['email'])) {
			$this->invalidateField('email', 'Please enter a valid e-mail address.');
		} else if ($this->shouldInsert()) {
			// Also check that the e-mail doesn't already exist in the database.
			if ($this->isEmailTaken($data['email'])) {
				$this->invalidateField('email', 'That e-mail is already registered.');
			}
		}
		if (empty($data['password'])) {
			$this->invalidateField('password', 'Please provide a password.');
		}
	}

In the above example, we check that some basic fields are specified (`first_name`, `last_name`, `email`, `password`) and that the `email` field is both a valid e-mail and, if we are performing an insert on save, that it doesn't already exist in the database.

Validation Outside the Model
----------------------------

Sometimes we need to validate data that won't actually be saved. For example, when we have the user enter their password twice we want to ensure they match but only save one. We can accomplish this by making calls to `invalidateField()` outside of `doValidateData()`:

	$user->invalidateField('password', 'Passwords must match.');

Here is a full example using the above:

	$data = $_POST['User']; // data submitted by a registration form.
	$errors = array();      // no errors so far.
	$user = new User();
	$user->setFields($data);
	if ($data['password'] != $data['password_confirm']) {
		$user->invalidateField('password', 'Passwords must match.');
	}
	// save() will call the validate function even though we already
	// invalidated one field. This ensures we get all the error messages
	// from the model in addition to the above.
	if (!$user->save()) {
		if (!$user->isDataValid()) {
			$errors = $user->getValidationErrors();
		}
	}
	// Pass errors on to the view, which can display them next to each field that had an error.
	$this->setViewVar('errors', $errors);

Note that we could also invalidate just the confirmation password field, even though the model won't save the password confirmation field. Validation is determined merely by whether or not validation errors exist, not whether or not they exist on the fields that would be saved. This allows you to have the error message stay with the appropriate field.

For example, it might make more sense to show the "Passwords must match" message next to the password confirmation field:

	if ($data['password'] != $data['password_confirm']) {
		$user->invalidateField('password_confirm', 'Passwords must match.');
	}

Or to show the message next to both fields:

	if ($data['password'] != $data['password_confirm']) {
		$user->invalidateField('password', 'Passwords must match.');
		$user->invalidateField('password_confirm', 'Passwords must match.');
	}

Other Validation Related Functions
----------------------------------

Cough's `save()` method calls `validateData()` which, if it hasn't been called before, calls `doValidateData()`. You can call `validateData()` yourself if you want to validate data before attempting a save. Just be sure to pass it the data to validate:

	$user->validateData($data);
	if ($user->isDataValid()) {
		// all good
		$user->save();
	} else {
		// one or more errors:
		$errors = $user->getValidationErrors();
	}

You can clear the errors in order to have `validateData()` run again:

	$user->clearValidationErrors();

