const express = require('express');
const path = require('path');
const bodyParser = require('body-parser');
const mongoose = require('mongoose');

const app = express();

// const port = 3000;

app.set('port', process.env.PORT || 8080);

app.get('/', function(req, res){
    res.send('hello world');
});

app.listen(app.get('port'));