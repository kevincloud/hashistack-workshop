package com.javaperks.api.db;

import java.util.ArrayList;
import java.util.List;
// import java.util.Date;
import java.lang.Integer;

import java.sql.DriverManager;
import java.sql.Statement;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class CustomerDb
{
	private static final Logger LOGGER = LoggerFactory.getLogger(CustomerDb.class);

    private String connstr;
    private String dbuser;
    private String dbpass;
    private String dbserver;
    private String database;

    public CustomerDb(String server, String database, String username, String password)
    {
        this.dbserver = server;
        this.database = database;
        this.dbuser = username;
        this.dbpass = password;

        connstr = "jdbc:mysql://" + this.dbserver + "/" + this.database + "?useSSL=false";
    }

    public List<Customer> getCustomers() {
        LOGGER.info("Get all customers");
        List<Customer> customers = new ArrayList<Customer>();

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_main";
            Statement s = cn.createStatement();
            ResultSet rs = s.executeQuery(sql);

            while(rs.next()) {
                customers.add(new Customer(rs.getInt("custid"), rs.getString("custno"), rs.getString("firstname"), rs.getString("lastname"), rs.getString("email"), rs.getString("dob"), rs.getString("ssn"), rs.getDate("datecreated")));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        for (Customer c : customers) {
            try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
            {
                String sql = "select * from customer_addresses where custid = " + Integer.toString(c.getCustId());
                Statement s = cn.createStatement();
                ResultSet rs = s.executeQuery(sql);
                List<Address> list = new ArrayList<Address>();
    
                while(rs.next()) {
                    Address a = new Address(
                        rs.getInt("addrid"),
                        rs.getInt("custid"),
                        rs.getString("contact"),
                        rs.getString("address1"),
                        rs.getString("address2"),
                        rs.getString("city"),
                        rs.getString("state"),
                        rs.getString("zip"),
                        rs.getString("phone"),
                        rs.getString("addrtype")
                    );
                    
                    list.add(a);
                }

                c.setAddresses(list);
            } catch (SQLException ex) {
                ex.printStackTrace();
            }
        }

        return customers;
    }

    public Address getAddressById(int addrid) {
        Address address = null;

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_addresses where addrid = " + Integer.toString(addrid);
            Statement s = cn.createStatement();
            ResultSet rs = s.executeQuery(sql);

            while(rs.next()) {
                address = new Address(
                    rs.getInt("addrid"),
                    rs.getInt("custid"),
                    rs.getString("contact"),
                    rs.getString("address1"),
                    rs.getString("address2"),
                    rs.getString("city"),
                    rs.getString("state"),
                    rs.getString("zip"),
                    rs.getString("phone"),
                    rs.getString("addrtype")
                );
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        return address;
    }

    public Status updatePersonalInfo(Customer customer) {
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "update customer_main set " +
                "firstname = '" + customer.getFirstName().replace("'", "''") + "', " +
                "lastname = '" + customer.getLastName().replace("'", "''") + "', " +
                "dob = '" + customer.getDOB().replace("'", "''") + "', " +
                "ssn = '" + customer.getSSN().replace("'", "''") + "' " +
            "where custid = " + Integer.toString(customer.getCustId());
            Statement s = cn.createStatement();
            s.executeUpdate(sql);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }

    public Status updateEmailAddress(Customer customer) {
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "update customer_main set " +
                "email = '" + customer.getEmail().replace("'", "''") + "' " +
            "where custid = " + Integer.toString(customer.getCustId());
            Statement s = cn.createStatement();
            s.executeUpdate(sql);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }

    public Status deletePayment(int payid) {
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "delete from customer_payment " +
            "where payid = " + Integer.toString(payid);
            Statement s = cn.createStatement();
            s.executeUpdate(sql);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }

    public Status updateAddress(Address address) {
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "update customer_addresses set " +
                "contact = '" + address.getContact().replace("'", "''") + "', " +
                "address1 = '" + address.getAddress1().replace("'", "''") + "', " +
                "address2 = '" + address.getAddress2().replace("'", "''") + "', " +
                "city = '" + address.getCity().replace("'", "''") + "', " +
                "state = '" + address.getState().replace("'", "''") + "', " +
                "zip = '" + address.getZip().replace("'", "''") + "', " +
                "phone = '" + address.getPhone().replace("'", "''") + "' " +
            "where custid = " + Integer.toString(address.getCustId()) + " " +
                "and addrtype = '" + address.getAddrType() + "'";
            Statement s = cn.createStatement();
            s.executeUpdate(sql);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }

    public Customer getCustomerById(String id) {
        LOGGER.info("Get a customer by custid");
        Customer customer = null;

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_main where custno = '" + id + "'";
            Statement s = cn.createStatement();
            ResultSet rs = s.executeQuery(sql);

            while(rs.next()) {
                customer = new Customer(rs.getInt("custid"), rs.getString("custno"), rs.getString("firstname"), rs.getString("lastname"), rs.getString("email"), rs.getString("dob"), rs.getString("ssn"), rs.getDate("datecreated"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_addresses where custid = " + Integer.toString(customer.getCustId());
            Statement s = cn.createStatement();
            ResultSet rs = s.executeQuery(sql);
            List<Address> list = new ArrayList<Address>();

            while(rs.next()) {
                Address a = new Address(
                    rs.getInt("addrid"),
                    rs.getInt("custid"),
                    rs.getString("contact"),
                    rs.getString("address1"),
                    rs.getString("address2"),
                    rs.getString("city"),
                    rs.getString("state"),
                    rs.getString("zip"),
                    rs.getString("phone"),
                    rs.getString("addrtype")
                );
                
                list.add(a);
            }

            customer.setAddresses(list);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        return customer;
    }

    public List<Payment> getPaymentsByCustomerId(String custId) {
        LOGGER.info("Get all credit cards for a customer");
        List<Payment> payments = new ArrayList<Payment>();

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_payment where custid = " + custId;
            Statement s = cn.createStatement();
            ResultSet rs = s.executeQuery(sql);

            while(rs.next()) {
                payments.add(new Payment(
                    rs.getInt("payid"), 
                    rs.getInt("custid"), 
                    rs.getString("cardname"), 
                    rs.getString("cardnumber"), 
                    rs.getString("cardtype"), 
                    rs.getString("cvv"), 
                    rs.getString("expmonth"), 
                    rs.getString("expyear"))
                );
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        return payments;
    }

    public Status updatePayment(Payment payment) {
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "update customer_payment set " +
                "cardname = '" + payment.getCardName().replace("'", "''") + "', " +
                "cardnumber = '" + payment.getCardNumber().replace("'", "''") + "', " +
                "cardtype = '" + payment.getCardType().replace("'", "''") + "', " +
                "cvv = '" + payment.getCVV().replace("'", "''") + "', " +
                "expmonth = '" + payment.getExpirationMonth().replace("'", "''") + "', " +
                "expyear = '" + payment.getExpirationYear().replace("'", "''") + "' " +
            "where payid = " + Integer.toString(payment.getPayId()) + " ";
            Statement s = cn.createStatement();
            s.executeUpdate(sql);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }

    public Status addPayment(Payment payment) {
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "insert into customer_payment values (" +
                "'" + Integer.toString(payment.getCustId()) + "'" +
                "'" + payment.getCardName().replace("'", "''") + "', " +
                "'" + payment.getCardNumber().replace("'", "''") + "', " +
                "'" + payment.getCardType().replace("'", "''") + "', " +
                "'" + payment.getCVV().replace("'", "''") + "', " +
                "'" + payment.getExpirationMonth().replace("'", "''") + "', " +
                "'" + payment.getExpirationYear().replace("'", "''") + "') " +
            "where payid = " + Integer.toString(payment.getPayId());
            Statement s = cn.createStatement();
            LOGGER.info(sql);
            s.executeUpdate(sql);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }
}
