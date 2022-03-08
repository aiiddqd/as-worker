'use strict';

/**
 * main service.
 */

const { createCoreService } = require('@strapi/strapi').factories;

module.exports = createCoreService('api::main.main');
