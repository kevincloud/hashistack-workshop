package com.javaperks.api.db;

public class Payment
{
    private int payId;
    private int custId;
    private String cardName;
    private String cardNumber;
    private String cardType;
    private String cvv;
    private String expMonth;
    private String expYear;

    public Payment() {
    }

    public Payment(int payId, int custId, String cardName, String cardNumber, String cardType, String cvv, String expMonth, String expYear) {
        this.payId = payId;
        this.custId = custId;
        this.cardName = cardName;
        this.cardNumber = cardNumber;
        this.cardType = cardType;
        this.cvv = cvv;
        this.expMonth = expMonth;
        this.expYear = expYear;
    }

    public int getPayId() {
        return this.payId;
    }

    public void setPayId(int payId) {
        this.payId = payId;
    }

    public int getCustId() {
        return this.custId;
    }

    public void setCustId(int custId) {
        this.custId = custId;
    }

    public String getCardName() {
        return this.cardName;
    }

    public void setCardName(String cardName) {
        this.cardName = cardName;
    }

    public String getCardNumber() {
        return this.cardNumber;
    }

    public void setCardNumber(String cardNumber) {
        this.cardNumber = cardNumber;
    }

    public String getCardType() {
        return this.cardType;
    }

    public void setCardType(String cardType) {
        this.cardType = cardType;
    }

    public String getCVV() {
        return this.cvv;
    }

    public void setCVV(String cvv) {
        this.cvv = cvv;
    }

    public String getExpirationMonth() {
        return this.expMonth;
    }

    public void setExpirationMonth(String expMonth) {
        this.expMonth = expMonth;
    }

    public String getExpirationYear() {
        return this.expYear;
    }

    public void setExpirationYear(String expYear) {
        this.expYear = expYear;
    }

    @Override
    public String toString() {
        String out = "{\n";
        out += "    \"payId\": \"" + this.payId + "\"\n";
        out += "    \"custId\": \"" + this.custId + "\"\n";
        out += "    \"cardName\": \"" + this.cardName + "\"\n";
        out += "    \"cardNumber\": \"" + this.cardNumber + "\"\n";
        out += "    \"cardType\": \"" + this.cardType + "\"\n";
        out += "    \"cvv\": \"" + this.cvv + "\"\n";
        out += "    \"expMonth\": \"" + this.expMonth + "\"\n";
        out += "    \"expYear\": \"" + this.expYear + "\"\n";
        out += "}\n";

        return out;
    }
}