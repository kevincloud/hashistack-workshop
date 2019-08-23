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

public class PaymentDb
{
	private static final Logger LOGGER = LoggerFactory.getLogger(CustomerDb.class);

    private String connstr;
    private String dbuser;
    private String dbpass;
    private String dbserver;
    private String database;

    public PaymentDb(String server, String database, String username, String password)
    {
        this.dbserver = server;
        this.database = database;
        this.dbuser = username;
        this.dbpass = password;

        connstr = "jdbc:mysql://" + this.dbserver + "/" + this.database + "?useSSL=false";
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
        LOGGER.info("Update a credit card");
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
        LOGGER.info("Add a new credit card");
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "insert into customer_payment values (" +
                "'" + Integer.toString(payment.getCustId()) + "'" +
                "'" + payment.getCardName().replace("'", "''") + "', " +
                "'" + payment.getCardNumber().replace("'", "''") + "', " +
                "'" + payment.getCardType().replace("'", "''") + "', " +
                "'" + payment.getCVV().replace("'", "''") + "', " +
                "'" + payment.getExpirationMonth().replace("'", "''") + "', " +
                "'" + payment.getExpirationYear().replace("'", "''") + "') ";
            Statement s = cn.createStatement();
            s.executeUpdate(sql);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }
}
