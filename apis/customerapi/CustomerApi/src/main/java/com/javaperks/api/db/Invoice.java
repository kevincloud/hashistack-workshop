package com.javaperks.api.db;

import java.util.Date;
// import java.text.SimpleDateFormat;

public class Invoice
{
    private int invId;
    private int custId;
    private Date invDate;
    private String orderId;
    private double amount;
    private double tax;
    private double shipping;
    private double total;
    private Date datePaid;
    private String contact;
    private String address1;
    private String address2;
    private String city;
    private String state;
    private String zip;
    private String phone;

    public Invoice() {
    }

    public Invoice(int invId, int custId, Date invDate, String orderId, double amount, double tax, double shipping, double total, Date datePaid, String contact, String address1, String address2, String city, String state, String zip, String phone) {
        this.invId = invId;
        this.custId = custId;
        this.invDate = invDate;
        this.orderId = orderId;
        this.amount = amount;
        this.tax = tax;
        this.total = total;
        this.datePaid = datePaid;
        this.contact = contact;
        this.address2 = address2;
        this.city = city;
        this.state = state;
        this.zip = zip;
        this.phone = phone;
    }

    public int getInvoiceId() {
        return this.invId;
    }

    public void setInvoiceId(int invId) {
        this.invId = invId;
    }

    public int getCustId() {
        return this.custId;
    }

    public void setCustId(int custId) {
        this.custId = custId;
    }

    public Date getInvoiceDate() {
        return this.invDate;
    }

    public void setInvoiceDate(Date invDate) {
        this.invDate = invDate;
    }

    public String getOrderId() {
        return this.orderId;
    }

    public void setOrderId(String orderId) {
        this.orderId = orderId;
    }

    public double getAmount() {
        return this.amount;
    }

    public void setAmount(double amount) {
        this.amount = amount;
    }

    public double getTax() {
        return this.tax;
    }

    public void setTax(double tax) {
        this.tax = tax;
    }

    public double getShipping() {
        return this.shipping;
    }

    public void setShipping(double shipping) {
        this.shipping = shipping;
    }

    public double getTotal() {
        return this.total;
    }

    public void setTotal(double total) {
        this.total = total;
    }

    public Date getDatePaid() {
        return this.datePaid;
    }

    public void setDatePaid(Date datePaid) {
        this.datePaid = datePaid;
    }

    public String getContact() {
        return this.contact;
    }

    public void setContact(String contact) {
        this.contact = contact;
    }

    public String getAddress1() {
        return this.address1;
    }

    public void setAddress1(String address) {
        this.address1 = address;
    }

    public String getAddress2() {
        return this.address2;
    }

    public void setAddress2(String address) {
        this.address2 = address;
    }

    public String getCity() {
        return this.city;
    }

    public void setCity(String city) {
        this.city = city;
    }

    public String getState() {
        return this.state;
    }

    public void setState(String state) {
        this.state = state;
    }

    public String getZip() {
        return this.zip;
    }

    public void setZip(String zip) {
        this.zip = zip;
    }

    public String getPhone() {
        return this.phone;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }

    @Override
    public String toString() {
        String out = "";

        return out;
    }
}