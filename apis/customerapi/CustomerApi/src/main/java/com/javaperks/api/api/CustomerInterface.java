package com.javaperks.api.api;

import java.util.ArrayList;
import java.util.List;

import org.json.JSONArray;
import org.json.JSONObject;

import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.client.Client;
import javax.ws.rs.client.Invocation;
import javax.ws.rs.client.WebTarget;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;

import com.javaperks.api.db.*;
import com.bettercloud.vault.*;

@Produces(MediaType.APPLICATION_JSON)
@Path("/customers")
public class CustomerInterface
{
    private Vault vault;
    private String dbserver;
    private String username;
    private String password;
    private String database;

    public CustomerInterface() throws Exception {
        VaultConfig vaultConfig;

        try
        {
            vaultConfig = new VaultConfig()
            .address("")
            .token("")
            .build();
        } catch ( VaultException ex) {
            throw new Exception("Could not initialize Vault session.");
        }

        this.vault = new Vault(vaultConfig);

        this.dbserver = "kevin-mysql-test.cd2ntnfz8tii.us-east-1.rds.amazonaws.com";
        this.username = "root";
        this.password = "SuperFuzz1";
        this.database = "javaperks";
    }

    @GET
    // @Path("/customers/")
    public Response getCustomers()
    {
        CustomerDb cdb = new CustomerDb(this.dbserver, this.database, this.username, this.password);

        return Response.ok(cdb.getCustomers()).build();
    }
}