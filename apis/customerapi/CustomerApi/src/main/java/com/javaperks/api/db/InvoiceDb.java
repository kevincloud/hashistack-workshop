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

public class InvoiceDb
{
	private static final Logger LOGGER = LoggerFactory.getLogger(CustomerDb.class);

    private String connstr;
    private String dbuser;
    private String dbpass;
    private String dbserver;
    private String database;

    public InvoiceDb(String server, String database, String username, String password)
    {
        this.dbserver = server;
        this.database = database;
        this.dbuser = username;
        this.dbpass = password;

        connstr = "jdbc:mysql://" + this.dbserver + "/" + this.database + "?useSSL=false";
    }

    // public List<Invoice> getInvoicesByCustomerId(String custId) {
    //     LOGGER.info("Get all credit cards for a customer");
    //     List<Invoice> invoices = new ArrayList<Invoice>();

    //     try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
    //     {
    //         String sql = "select * from customer_payment where custid = " + custId;
    //         Statement s = cn.createStatement();
    //         ResultSet rs = s.executeQuery(sql);

    //         while(rs.next()) {
    //             invoices.add(new Invoice(
    //                 rs.getInt("payid"), 
    //                 rs.getInt("custid"), 
    //                 rs.getString("cardname"), 
    //                 rs.getString("cardnumber"), 
    //                 rs.getString("cardtype"), 
    //                 rs.getString("cvv"), 
    //                 rs.getString("expmonth"), 
    //                 rs.getString("expyear"))
    //             );
    //         }
    //     } catch (SQLException ex) {
    //         ex.printStackTrace();
    //     }

    //     return invoices;
    // }

    public Invoice getInvoiceById(String invid) {
        LOGGER.info("Get a specific credit card for a customer");
        Invoice invoice = null;
        List<InvoiceItem> items = new ArrayList<InvoiceItem>();

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_invoice_item where invid = " + invid;
            Statement s = cn.createStatement();
            ResultSet rs = s.executeQuery(sql);

            while(rs.next()) {
                items.add(new InvoiceItem(
                    rs.getInt("itemid"), 
                    rs.getInt("invid"), 
                    rs.getString("product"), 
                    rs.getString("description"), 
                    rs.getDouble("amount"),
                    rs.getInt("quantity"), 
                    rs.getInt("lineno")));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "select * from customer_invoice where invid = " + invid;
            Statement s = cn.createStatement();
            ResultSet rs = s.executeQuery(sql);

            while(rs.next()) {
                invoice = new Invoice(
                    rs.getInt("invid"), 
                    rs.getString("invno"), 
                    rs.getInt("custid"), 
                    rs.getDate("invdate"),
                    rs.getString("orderid"), 
                    rs.getString("title"), 
                    rs.getDouble("amount"),
                    rs.getDouble("tax"),
                    rs.getDouble("shipping"),
                    rs.getDouble("total"),
                    rs.getDate("datepaid"),
                    rs.getString("contact"), 
                    rs.getString("address1"), 
                    rs.getString("address2"), 
                    rs.getString("city"), 
                    rs.getString("state"), 
                    rs.getString("zip"), 
                    rs.getString("phone"),
                    items);
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        return invoice;
    }

    // public Status updatePayment(Payment payment) {
    //     LOGGER.info("Update a credit card");
    //     try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
    //     {
    //         String sql = "update customer_payment set " +
    //             "cardname = '" + payment.getCardName().replace("'", "''") + "', " +
    //             "cardnumber = '" + payment.getCardNumber().replace("'", "''") + "', " +
    //             "cardtype = '" + payment.getCardType().replace("'", "''") + "', " +
    //             "cvv = '" + payment.getCVV().replace("'", "''") + "', " +
    //             "expmonth = '" + payment.getExpirationMonth().replace("'", "''") + "', " +
    //             "expyear = '" + payment.getExpirationYear().replace("'", "''") + "' " +
    //         "where payid = " + Integer.toString(payment.getPayId()) + " ";
    //         Statement s = cn.createStatement();
    //         s.executeUpdate(sql);
    //     } catch (SQLException ex) {
    //         ex.printStackTrace();
    //     }

    //     Status status = new Status(true, "Success!");
    //     return status;
    // }

    public Status addInvoice(Invoice invoice) {
        LOGGER.info("Add a new credit card");
        int invid = 0;
        ResultSet rs = null;
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "insert into customer_invoice " + 
                "(invno, custid, invdate, orderid, title, amount, tax, shipping, total, datepaid, contact, address1, address2, city, state, zip, phone) values (" +
                "'" + invoice.getInvoiceNumber() + "', " +
                "'" + invoice.getCustId() + "', " +
                "'" + invoice.getInvoiceDate() + "', " +
                "'" + invoice.getOrderId() + "', " +
                "'" + invoice.getTitle() + "', " +
                "'" + invoice.getAmount() + "', " +
                "'" + invoice.getTax() + "', " +
                "'" + invoice.getShipping() + "', " +
                "'" + invoice.getTotal() + "', " +
                "'" + invoice.getDatePaid() + "', " +
                "'" + invoice.getContact() + "', " +
                "'" + invoice.getAddress1() + "', " +
                "'" + invoice.getAddress2() + "', " +
                "'" + invoice.getCity() + "', " +
                "'" + invoice.getState() + "', " +
                "'" + invoice.getZip() + "', " +
                "'" + invoice.getPhone().replace("'", "''") + "') ";
            Statement s = cn.createStatement();
            s.executeUpdate(sql, Statement.RETURN_GENERATED_KEYS);
            rs = s.getGeneratedKeys();
            while (rs.next()) {
                invid = rs.getInt(1);
            } 
            for (InvoiceItem item : invoice.getItems()) {
                String isql = "insert into customer_invoice_item " +
                    "(invid, product, description, amount, quantity, lineno) " +
                    "'" + invid + "', " +
                    "'" + item.getProduct() + "', " +
                    "'" + item.getDescription() + "', " +
                    "'" + item.getAmount() + "', " +
                    "'" + item.getAmount() + "', " +
                    "'" + item.getLineNumber() + "')";
                s.executeUpdate(isql);
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "Success!");
        return status;
    }
}
