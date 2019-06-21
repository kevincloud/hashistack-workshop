var express = require('express'),
    app = express(),
    port = process.env.PORT || 5823,
    aws = require('aws-sdk'),
    bodyParser = require('body-parser');

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

var routes = require('./routes/appRoutes.js');
routes(app);

app.listen(port);

console.log('Server started on port ' + port);

