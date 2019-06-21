package com.javaperks.api;

import io.dropwizard.Application;
import io.dropwizard.Configuration;
import io.dropwizard.setup.Bootstrap;
import io.dropwizard.setup.Environment;
import io.dropwizard.client.JerseyClientBuilder;

import com.javaperks.api.api.CustomerInterface;

import javax.ws.rs.client.Client;

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
    public void run(CustomerApiConfiguration c, Environment e) {
        LOGGER.info("Registering API Resources");

        // Client client = new JerseyClientBuilder(e).build("CustomerApiClient");
        e.jersey().register(new CustomerInterface());
    }

}
