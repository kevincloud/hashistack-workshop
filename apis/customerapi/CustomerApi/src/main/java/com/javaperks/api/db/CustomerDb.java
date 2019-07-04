package com.javaperks.api.db;

import java.util.ArrayList;
import java.util.List;
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

        connstr = "jdbc:mysql://" + this.dbserver + ":3306/" + this.database + "?useSSL=false";
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

    public Customer getCustomerById(int id) {
        LOGGER.info("Get a customer by custid");
        Customer customer = null;

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_main where custid = " + Integer.toString(id);
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
            String sql = "select * from customer_addresses where custid = ";
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
}