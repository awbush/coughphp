

person table
	person_id name
	1         Anthony
	
role table
	role_id role
	1       student
	2       professor
	
school table
	school_id short_name name
	1         TTU        Texas Tech University
	2         UT         University of Texas

enrollment table:
	person_id  role_id   school_id  start_date  end_date  is_retired
	1          1         1          2005        2006      0
	1          2         1          2006        2007      0
	aka
	Anthony    student   TTU        2005        2006      0
	Anthony    professor TTU        2006        2007      0

school_rating table
	person_id school_id rating
	1         1         3
    1         2         2


BAH


product
	product_id
	name
product2os
	product_id
	os_id
os
	os_id
	name


getProduct2Os_Collection()
->getOs()