'use strict';

/**
 *  main controller
 */

const { createCoreController } = require('@strapi/strapi').factories;

module.exports = createCoreController('api::main.main');
