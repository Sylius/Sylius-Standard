// In this file you can import assets like images or stylesheets
console.log('Hello Webpack Encore! Edit me in assets/admin/entrypoint.js');

import '@vendor/sylius/cms-plugin/assets/admin/entrypoint';
import '../../vendor/sylius/refund-plugin/assets/entrypoint';
import '../../vendor/sylius/return-plugin/assets/admin/entrypoint';

import './styles/custom.scss'

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
