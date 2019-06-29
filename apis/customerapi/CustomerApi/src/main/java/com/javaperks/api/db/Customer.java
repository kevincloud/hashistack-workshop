package com.javaperks.api.db;

import java.util.Date;
import java.util.List;

public class Customer
{
    private int custId;
    private String custNo;
    private String firstName;
    private String lastName;
    private String email;
    private String dob;
    private String ssn;
    private Date created;
    private List<Address> addresses;

    public Customer() {
    }

    public Customer(int custId, String custNo, String firstName, String lastName, String email, String dob, String ssn, Date created) {
        this.custId = custId;
        this.custNo = custNo;
        this.firstName = firstName;
        this.lastName = lastName;
        this.email = email;
        this.dob = dob;
        this.ssn = ssn;
        this.created = created;
    }

    public int getCustId() {
        return this.custId;
    }

    public void setCustId(int custId) {
        this.custId = custId;
    }

    public String getCustNo() {
        return this.custNo;
    }

    public void setCustNo(String custNo) {
        this.custNo = custNo;
    }

    public String getFirstName() {
        return this.firstName;
    }

    public void setFirstName(String firstName) {
        this.firstName = firstName;
    }

    public String getLastName() {
        return this.lastName;
    }

    public void setLastName(String lastName) {
        this.lastName = lastName;
    }

    public String getEmail() {
        return this.email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getDOB() {
        return this.dob;
    }

    public void setDOB(String DOB) {
        this.dob = DOB;
    }

    public String getSSN() {
        return this.ssn;
    }

    public void setSSN(String SSN) {
        this.ssn = SSN;
    }

    public Date getDateCreated() {
        return this.created;
    }

    public void setDateCreated(Date dateCreated) {
        this.created = dateCreated;
    }

    public List<Address> getAddresses() {
        return this.addresses;
    }

    public void setAddresses(List<Address> addresses) {
        this.addresses = addresses;
    }

    @Override
    public String toString() {
        return this.firstName + " " + this.lastName;
    }
}