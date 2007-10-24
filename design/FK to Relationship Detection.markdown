gen the collection accessors, BUT change the linkRelationships to check for pre-existing one-to-many relationships before adding another potentially conflicting relationship.  That is, avoid:

	address->getTicket_Collection()
	address->getTicket_Collection()

for the relationship:

	ticket
		billing_address_id
		shipping_address_id

instead either ignore the collection side all together or generate non-conflicting names:

	address->getBillingTicket_Collection()
	address->getShippingTicket_Collection()

but, that feels too magical.

This is not an issue yet, but if you set id_regex so that it pulls in common prefixes to the table names, then it will add the "FK" for the relationship (or if you specify the FK yourself, for that matter).  So, there has to be some kind of config that says "generate has one for this" and "generate has many for this" and have them be separate.
	

I'm starting to think linkRelationships should not be in the Schema class b/c the conversion from FK -> relationship might need to be customized by the user...

Perhaps the quickest thing to do is to add to methods to the SchemaForeignKey object:

	shouldGenerateHasOneRelationship
	shouldGenerateHasManyRelationship

AND it is up to the FK adder to set them... (default both to false?)


THE ANSWER IS HERE (generate always for now, don't worry about the above yet).

To avoid the conflicts, we check (look ahead) to see if we will be creating a conflict, and if so we change the accessors by appending "By" . title_case($remoteFieldName).  For example:

getTicket_Collection_ByBillingAddressId()


getTicket_Collection()
	A) if ticket.column_name == address.pk, then don't do _ByColumName()?
	OR
	B) if address.pk links to more than one column on ticket, then append _ByColumnName to each.

	way B for sure.