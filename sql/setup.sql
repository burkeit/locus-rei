insert into Things set Type='Location', Barcode='LOC:NULL', Location_ID=NULL, ID=1;
insert into Location set Name='Unknown Location', ID=1;
insert into Things set Type='Department', Barcode='DEPT:NULL', ID=2;
insert into Department set name='No Department', Owner_ID=NULL, ID=2;
insert into Things set Type='Person', Barcode='PERSON:NULL', ID=3;
insert into Person set LastName='Nobody', Department_ID=2, ID=3;
update Things set Location_ID=1 where ID=1;
update Department set Owner_ID=3 where ID=2;
