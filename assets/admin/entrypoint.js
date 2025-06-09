import './bootstrap.js';

const imagesContext = require.context('./images', true, /\.(jpg|jpeg|png|svg|gif|webp)$/);
imagesContext.keys().forEach(imagesContext);


// In this file you can import assets like images or stylesheets
console.log('Hello Webpack Encore! Edit me in assets/admin/entrypoint.js');
