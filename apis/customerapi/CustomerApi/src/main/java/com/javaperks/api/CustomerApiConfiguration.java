package com.javaperks.api;

import io.dropwizard.Configuration;
import com.fasterxml.jackson.annotation.JsonProperty;
import javax.validation.constraints.*;

public class CustomerApiConfiguration extends Configuration {
    @NotNull
    private String vaultAddress;
    @NotNull
    private String vaultToken;
    @NotNull
    private String dbAddress;
    @NotNull
    private String dbUsername;
    @NotNull
    private String dbPassword;
    @NotNull
    private String dbDatabase;

    @JsonProperty
    public String getVaultAddress() {
        return vaultAddress;
    }

    @JsonProperty
    public String getVaultToken() {
        return vaultToken;
    }

    @JsonProperty
    public String getDbAddress() {
        return dbAddress;
    }

    @JsonProperty
    public String getDbUsername() {
        return dbUsername;
    }

    @JsonProperty
    public String getDbPassword() {
        return dbPassword;
    }

    @JsonProperty
    public String getDbDatabase() {
        return dbDatabase;
    }
}
