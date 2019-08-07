package com.javaperks.api.db;

public class Status
{
    private boolean status;
    private String message;

    public Status() {
    }

    public Status(boolean status, String message) {
        this.status = status;
        this.message = message;
    }

    public boolean getStatus() {
        return this.status;
    }

    public void setStatus(boolean status) {
        this.status = status;
    }

    public String getMessage() {
        return this.message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    @Override
    public String toString() {
        String out = "(Hidden)";

        return out;
    }
}