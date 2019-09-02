package com.javaperks.api;

import io.dropwizard.Application;
import io.dropwizard.jersey.jackson.JsonProcessingExceptionMapper;
// import io.dropwizard.Configuration;
import io.dropwizard.setup.Bootstrap;
import io.dropwizard.setup.Environment;
// import io.dropwizard.client.JerseyClientBuilder;

import com.javaperks.api.api.CustomerInterface;
import com.javaperks.api.api.PaymentInterface;
import com.javaperks.api.api.InvoiceInterface;

// import javax.ws.rs.client.Client;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class CustomerApiApplication extends Application<CustomerApiConfiguration> {
	private static final Logger LOGGER = LoggerFactory.getLogger(CustomerApiApplication.class);

    public static void main(final String[] args) throws Exception {
        new CustomerApiApplication().run(args);
    }

    @Override
    public String getName() {
        return "CustomerApi";
    }

    @Override
    public void initialize(Bootstrap<CustomerApiConfiguration> bootstrap) {
    }

    @Override
    public void run(CustomerApiConfiguration c, Environment e) throws Exception {
        LOGGER.info("Registering API Resources");

        final CustomerInterface ci = new CustomerInterface(e.getValidator(), c.getVaultAddress(), c.getVaultToken());
        final PaymentInterface pi = new PaymentInterface(e.getValidator(), c.getVaultAddress(), c.getVaultToken());
        final InvoiceInterface ii = new InvoiceInterface(e.getValidator(), c.getVaultAddress(), c.getVaultToken());

        e.jersey().register(new JsonProcessingExceptionMapper(true));
        e.jersey().register(ci);
        e.jersey().register(pi);
        e.jersey().register(ii);
    }

}
