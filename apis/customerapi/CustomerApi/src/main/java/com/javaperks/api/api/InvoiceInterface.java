package com.javaperks.api.api;

// import javax.validation.ConstraintViolation;
import javax.validation.Validator;
import javax.ws.rs.GET;
import javax.ws.rs.PUT;
import javax.ws.rs.POST;
import javax.ws.rs.DELETE;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;

import com.javaperks.api.db.*;
import com.bettercloud.vault.*;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

@Produces(MediaType.APPLICATION_JSON)
@Path("/invoice")
public class InvoiceInterface
{
	private static final Logger LOGGER = LoggerFactory.getLogger(CustomerDb.class);

    private Vault vault;
    private String vaultaddr;
    private String vaulttoken;
    private String dbserver;
    private String username;
    private String password;
    private String database;
    private final Validator validator;

    public InvoiceInterface(Validator validator, String vaultAddress, String vaultToken) throws Exception {
        VaultConfig vaultConfig;

        this.validator = validator;
        this.vaultaddr = vaultAddress;
        this.vaulttoken = vaultToken;

        try
        {
            vaultConfig = new VaultConfig()
            .address(this.vaultaddr)
            .token(this.vaulttoken)
            .build();
        } catch ( VaultException ex) {
            throw new Exception("Could not initialize Vault session.");
        }

        this.vault = new Vault(vaultConfig);

        this.dbserver = vault.logical().read("secret/dbhost").getData().get("address");
        this.username = vault.logical().read("secret/dbhost").getData().get("username");
        this.password = vault.logical().read("secret/dbhost").getData().get("password");
        this.database = vault.logical().read("secret/dbhost").getData().get("database");
    }

    @POST
    public Response addInvoice(Invoice invoice) {
        InvoiceDb idb = new InvoiceDb(this.dbserver, this.database, this.username, this.password);
        return Response.ok(idb.addInvoice(invoice)).build();
    }
}