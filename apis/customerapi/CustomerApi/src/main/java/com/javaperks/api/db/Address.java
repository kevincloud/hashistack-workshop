package com.javaperks.api.db;

public class Address
{
    private int addrId;
    private int custId;
    private String contact;
    private String address1;
    private String address2;
    private String city;
    private String state;
    private String zip;
    private String phone;
    private String addrType;

    public Address() {
    }

    public Address(int addrId, int custId, String contact, String address1, String address2, String city, String state, String zip, String phone, String addrType) {
        this.addrId = addrId;
        this.custId = custId;
        this.contact = contact;
        this.address1 = address1;
        this.address2 = address2;
        this.city = city;
        this.state = state;
        this.zip = zip;
        this.phone = phone;
        this.addrType = addrType;
    }

    public int getAddrId() {
        return this.addrId;
    }

    public void setAddrId(int addrId) {
        this.addrId = addrId;
    }

    public int getCustId() {
        return this.custId;
    }

    public void setCustId(int custId) {
        this.custId = custId;
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

    public String getAddrType() {
        return this.addrType;
    }

    public void setAddrType(String addrType) {
        this.addrType = addrType;
    }

    @Override
    public String toString() {
        String out = "";

        out += this.address1 + "\n";
        if (this.address2 != "") {
            out += this.address2 + "\n";
        }
        out += this.city + ", " + this.state + " " + this.zip + "\n";
        out += this.phone + "\n";
        return out;
    }
}