import MySQLdb # pylint: disable=import-error
import sys

dbname = sys.argv[1]
username = sys.argv[2]
password = sys.argv[3]

db = MySQLdb.connect(host = dbname,
                     user = username,
                     password = password)

cursor = db.cursor()

sql = "create database if not exists javaperks"
x = cursor.execute(sql)

sql = "use javaperks"
x = cursor.execute(sql)

sql = """create table if not exists customer_main(
    custid int auto_increment,
    custno varchar(20) not null,
    firstname varchar(50) not null,
    lastname varchar(50) not null,
    email varchar(255) not null,
    dob varchar(255),
    ssn varchar(255),
    datecreated datetime,
    primary key (custid),
    index idx_custno (custno)
) engine=innodb
"""
x = cursor.execute(sql)

sql = """create table if not exists customer_addresses(
    addrid int auto_increment,
    custid int not null,
    contact varchar(255) not null,
    address1 varchar(150) not null,
    address2 varchar(150),
    city varchar(150) not null,
    state varchar(2) not null,
    zip varchar(20) not null,
    phone varchar(35),
    addrtype varchar(20),
    primary key(addrid),
    index idx_custid (custid),
    constraint fk_custid_custid
        foreign key (custid)
        references customer_main (custid)
) engine=innodb
"""
x = cursor.execute(sql)

sql = """create table if not exists customer_payment(
    payid int auto_increment,
    custid int not null,
    cardname varchar(255) not null,
    cardnumber varchar(255) not null,
    cardtype varchar(2),
    cvv varchar(50) not null,
    expmonth varchar(2) not null,
    expyear varchar(4) not null,
    primary key(payid),
    index idx_pay_custid (custid)
) engine=innodb
"""
x = cursor.execute(sql)

sql = """create table if not exists customer_invoice(
    invid int auto_increment,
    custid int not null,
    invdate datetime not null,
    orderid varchar(20),
    amount decimal,
    tax decimal,
    shipping decimal,
    total decimal,
    datepaid datetime,
    contact varchar(255) not null,
    address1 varchar(150) not null,
    address2 varchar(150),
    city varchar(150) not null,
    state varchar(2) not null,
    zip varchar(20) not null,
    phone varchar(35),
    primary key(invid),
    index idx_inv_custid (custid)
) engine=innodb
"""

##################################
# Add Customer 1 - Janice Thompson
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS100312', 
        'Janice', 
        'Thompson', 
        'jthomp4423@example.com', 
        '11/28/1983', 
        '027-40-7057', 
        '2016-05-01'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Janice Thompson', "
        "'3611 Farland Street', "
        "'Brockton', "
        "'MA', "
        "'02401', "
        "'774-240-5996', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Janice Thompson', "
        "'3611 Farland Street', "
        "'Brockton', "
        "'MA', "
        "'02401', "
        "'774-240-5996', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Janice Thompson', "
        "'378282246310005', "
        "'AX', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 2 - James Wilson
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS106004', 
        'James', 
        'Wilson', 
        'wilson@example.com', 
        '6/4/1974', 
        '309-64-5158', 
        '2013-07-06'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'James Wilson', "
        "'1437 Capitol Avenue', "
        "'Paragon', "
        "'IN', "
        "'46166', "
        "'765-537-0152', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'James Wilson', "
        "'1437 Capitol Avenue', "
        "'Paragon', "
        "'IN', "
        "'46166', "
        "'765-537-0152', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'James Wilson', "
        "'371449635398431', "
        "'AX', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 3 - Tommy Ballinger
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS101438', 
        'Tommy', 
        'Ballinger', 
        'tommy6677@example.com', 
        '1/5/1984', 
        '530-02-6158', 
        '2016-12-28'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Tommy Ballinger', "
        "'2143 Wescam Court', "
        "'Reno', "
        "'NV', "
        "'89502', "
        "'775-856-9045', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Tommy Ballinger', "
        "'2143 Wescam Court', "
        "'Reno', "
        "'NV', "
        "'89502', "
        "'775-856-9045', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Tommy Ballinger', "
        "'378734493671000', "
        "'AX', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 4 - Mary McCann
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS210895', 
        'Mary', 
        'McCann', 
        'mmccann1212@example.com', 
        '9/4/1981', 
        '246-98-9817', 
        '2018-05-24'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Mary McCann', "
        "'4512 Layman Avenue', "
        "'Robbins', "
        "'NC', "
        "'27325', "
        "'910-948-3965', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Mary McCann', "
        "'4512 Layman Avenue', "
        "'Robbins', "
        "'NC', "
        "'27325', "
        "'910-948-3965', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Mary McCann', "
        "'6011111111111117', "
        "'DS', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 5 - Chris Peterson
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS122955', 
        'Chris', 
        'Peterson', 
        'cjpcomp@example.com', 
        '9/9/1975', 
        '019-26-9782', 
        '2015-03-04'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Chris Peterson', "
        "'2329 Joanne Lane', "
        "'Newburyport', "
        "'MA', "
        "'01950', "
        "'978-499-7306', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Chris Peterson', "
        "'2329 Joanne Lane', "
        "'Newburyport', "
        "'MA', "
        "'01950', "
        "'978-499-7306', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Chris Peterson', "
        "'6011000990139424', "
        "'DS', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 6 - Jennifer Jones
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS602934', 
        'Jennifer', 
        'Jones', 
        'jjhome7823@example.com', 
        '10/31/1983', 
        '209-62-4365', 
        '2014-10-17'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Jennifer Jones', "
        "'589 Hidden Valley Road', "
        "'Lancaster', "
        "'PA', "
        "'17670', "
        "'717-224-9902', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Jennifer Jones', "
        "'589 Hidden Valley Road', "
        "'Lancaster', "
        "'PA', "
        "'17670', "
        "'717-224-9902', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Jennifer Jones', "
        "'5555555555554444', "
        "'MC', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 7 - Clint Mason
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS157843', 
        'Clint', 
        'Mason', 
        'clint.mason312@example.com', 
        '10/7/1983', 
        '453-37-0205', 
        '2014-08-23'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Clint Mason', "
        "'3641 Alexander Drive', "
        "'Denton', "
        "'TX', "
        "'76201', "
        "'940-349-9386', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Clint Mason', "
        "'3641 Alexander Drive', "
        "'Denton', "
        "'TX', "
        "'76201', "
        "'940-349-9386', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Clint Mason', "
        "'5105105105105100', "
        "'MC', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 8 - Matt Grey
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS523484', 
        'Matt', 
        'Grey', 
        'greystone89@example.com', 
        '7/25/1963', 
        '184-36-8146', 
        '2016-11-12'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Matt Grey', "
        "'1320 Tree Top Lane', "
        "'Wayne', "
        "'PA', "
        "'19087', "
        "'610-225-6567', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Matt Grey', "
        "'1320 Tree Top Lane', "
        "'Wayne', "
        "'PA', "
        "'19087', "
        "'610-225-6567', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Matt Grey', "
        "'4111111111111111', "
        "'VI', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 9 - Howard Turner
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS658871', 
        'Howard', 
        'Turner', 
        'runwayyourway@example.com', 
        '6/29/1977', 
        '019-26-8577', 
        '2014-03-03'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Howard Turner', "
        "'1179 Lynn Street', "
        "'Woburn', "
        "'MA', "
        "'01801', "
        "'617-251-5420', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Howard Turner', "
        "'1179 Lynn Street', "
        "'Woburn', "
        "'MA', "
        "'01801', "
        "'617-251-5420', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Howard Turner', "
        "'4012888888881881', "
        "'VI', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

##################################
# Add Customer 10 - Larry Olsen
##################################

sql = """insert into customer_main(
        custno, 
        firstname, 
        lastname, 
        email,
        dob, 
        ssn, 
        datecreated
    ) values (
        'CS103393', 
        'Larry', 
        'Olsen', 
        'olsendog1979@example.com', 
        '4/17/1992', 
        '285-70-8598', 
        '2016-02-21'
    )
"""
x = cursor.execute(sql)

sql = "select last_insert_id()"
retval = cursor.execute(sql)
rset = cursor.fetchall()
nextid = rset[0][0]

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Larry Olsen', "
        "'2850 Still Street', "
        "'Oregon', "
        "'OH', "
        "'43616', "
        "'419-698-9890', "
        "'B'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_addresses("
        "custid, "
        "contact, "
        "address1, "
        "city, "
        "state, "
        "zip, "
        "phone, "
        "addrtype"
    ") values ("
        "" + str(nextid) + ", "
        "'Larry Olsen', "
        "'2850 Still Street', "
        "'Oregon', "
        "'OH', "
        "'43616', "
        "'419-698-9890', "
        "'S'"
    ")")
x = cursor.execute(sql)

sql = ("insert into customer_payment("
        "custid, "
        "cardname, "
        "cardnumber, "
        "cardtype, "
        "cvv, "
        "expmonth, "
        "expyear"
    ") values ("
        "" + str(nextid) + ", "
        "'Larry Olsen', "
        "'4111111111111111', "
        "'VI', "
        "'344', "
        "'08', "
        "'2024'"
    ")")
x = cursor.execute(sql)

db.commit()
db.close()
