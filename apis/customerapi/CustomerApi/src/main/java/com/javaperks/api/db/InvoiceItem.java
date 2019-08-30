package com.javaperks.api.db;

public class InvoiceItem
{
    private int itemId;
    private int invId;
    private String product;
    private String description;
    private double amount;
    private int quantity;
    private int lineNo;

    public InvoiceItem() {

    }

    public InvoiceItem(int itemId, int invId, String product, String description, double amount, int quantity, int lineNo) {
        this.itemId = itemId;
        this.invId = invId;
        this.product = product;
        this.description = description;
        this.amount = amount;
        this.quantity = quantity;
        this.lineNo = lineNo;
    }

    public int getItemId() {
        return this.itemId;
    }

    public void setItemId(int itemId) {
        this.itemId = itemId;
    }

    public int getInvoiceId() {
        return this.invId;
    }

    public void setInvoiceId(int invId) {
        this.invId = invId;
    }

    public String getProduct() {
        return this.product;
    }

    public void setProduct(String product) {
        this.product = product;
    }

    public String getDescription() {
        return this.description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public double getAmount() {
        return this.amount;
    }

    public void setAmount(double amount) {
        this.amount = amount;
    }

    public int getQuantity() {
        return this.quantity;
    }

    public void setQuantity(int quantity) {
        this.quantity = quantity;
    }

    public int getLineNumber() {
        return lineNo;
    }

    public void setLineNumber(int lineno) {
        this.lineNo = lineno;
    }
}
