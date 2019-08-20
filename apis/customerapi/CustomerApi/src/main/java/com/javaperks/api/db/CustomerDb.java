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
                String sql = "select * from customer_addresses";
                Statement s = cn.createStatement();
                ResultSet rs = s.executeQuery(sql);
                List<Address> list = new ArrayList<Address>();
    
                while(rs.next()) {
                    Address a = new Address(
                        rs.getInt("addrid"),
                        rs.getInt("custid"),
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

    public Status updateCustomer(Customer customer) {
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "update customer_main set " +
                "firstname = '" + customer.getFirstName().replace("'", "''") + "', " +
                "lastname = '" + customer.getFirstName().replace("'", "''") + "', " +
                "email = '" + customer.getFirstName().replace("'", "''") + "', " +
                "dob = '" + customer.getFirstName().replace("'", "''") + "', " +
                "ssn = '" + customer.getFirstName().replace("'", "''") + "', " +
            "where custno = '" + Integer.toString(customer.getCustId()) + "'";
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
        LOGGER.info("Get all customers");
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
}
