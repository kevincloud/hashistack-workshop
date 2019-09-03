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
	private static final Logger LOGGER = LoggerFactory.getLogger(InvoiceDb.class);

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

    public Invoice getInvoiceById(String invid) {
        LOGGER.info("Get a specific invoice");
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
                    rs.getString("invdate"),
                    rs.getString("orderid"), 
                    rs.getString("title"), 
                    rs.getDouble("amount"),
                    rs.getDouble("tax"),
                    rs.getDouble("shipping"),
                    rs.getDouble("total"),
                    rs.getString("datepaid"),
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

    public Status addInvoice(Invoice invoice) {
        LOGGER.info("Add a new invoice");
        int invid = 0;
        ResultSet rs = null;
        try (Connection cn = DriverManager.getConnection(this.connstr, this.dbuser, this.dbpass))
        {
            String sql = "insert into customer_invoice " + 
                "(invno, custid, invdate, orderid, title, amount, tax, shipping, total, datepaid, contact, address1, address2, city, state, zip, phone) values (" +
                "'" + invoice.getInvoiceNumber() + "', " +
                invoice.getCustId() + ", " +
                "'" + invoice.getInvoiceDate() + "', " +
                "'" + invoice.getOrderId() + "', " +
                "'" + invoice.getTitle().replace("'", "''") + "', " +
                invoice.getAmount() + ", " +
                invoice.getTax() + ", " +
                invoice.getShipping() + ", " +
                invoice.getTotal() + ", " +
                "'" + invoice.getDatePaid() + "', " +
                "'" + invoice.getContact().replace("'", "''") + "', " +
                "'" + invoice.getAddress1().replace("'", "''") + "', " +
                "'" + invoice.getAddress2().replace("'", "''") + "', " +
                "'" + invoice.getCity().replace("'", "''") + "', " +
                "'" + invoice.getState().replace("'", "''") + "', " +
                "'" + invoice.getZip().replace("'", "''") + "', " +
                "'" + invoice.getPhone().replace("'", "''") + "') ";
            System.out.println(sql);
            Statement s = cn.createStatement();
            s.executeUpdate(sql, Statement.RETURN_GENERATED_KEYS);
            rs = s.getGeneratedKeys();
            while (rs.next()) {
                invid = rs.getInt(1);
            } 
            for (InvoiceItem item : invoice.getItems()) {
                String isql = "insert into customer_invoice_item " +
                    "(invid, product, description, amount, quantity, lineno) values ( " +
                    Integer.toString(invid) + ", " +
                    "'" + item.getProduct().replace("'", "''") + "', " +
                    "'" + item.getDescription().replace("'", "''") + "', " +
                    Double.toString(item.getAmount()) + ", " +
                    Integer.toString(item.getQuantity()) + ", " +
                    Integer.toString(item.getLineNumber()) + ")";
                System.out.println(isql);
                s.executeUpdate(isql);
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }

        Status status = new Status(true, "{ \"invoiceId\": " + invid + " }");
        return status;
    }
}
