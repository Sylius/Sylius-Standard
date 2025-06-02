// In this file you can import assets like images or stylesheets
console.log('Hello Webpack Encore! Edit me in assets/admin/entrypoint.js');

const imagesContext = require.context('./images', true, /\.(jpg|jpeg|png|svg|gif|webp)$/);
imagesContext.keys().forEach(imagesContext);


// assets/admin/entrypoint.js

console.log('🎧 [ADMIN] message listener załadowany');
window.addEventListener('message', event => {
  console.log('📩 [ADMIN] odebrałem message', event.data, 'origin:', event.origin);
  const { type, vars } = event.data;
  if (type === 'updateAll' && typeof vars === 'object') {
    Object.entries(vars).forEach(([name, value]) => {
      document.documentElement.style.setProperty(name, value);
    });
  }
});
