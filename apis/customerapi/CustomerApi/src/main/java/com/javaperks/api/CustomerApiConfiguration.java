package com.javaperks.api;

import io.dropwizard.Configuration;
import com.fasterxml.jackson.annotation.JsonProperty;
import javax.validation.constraints.*;

public class CustomerApiConfiguration extends Configuration {
    @NotNull
    private String vaultAddress;
    @NotNull
    private String vaultToken;

    @JsonProperty
    public String getVaultAddress() {
        return vaultAddress;
    }

    @JsonProperty
    public String getVaultToken() {
        return vaultToken;
    }
}
