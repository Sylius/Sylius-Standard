// In this file you can import assets like images or stylesheets
console.log('Hello Webpack Encore! Edit me in assets/shop/entrypoint.js');

console.log('🎧 [SHOP] message listener załadowany');

window.addEventListener('message', event => {
  console.log('📩 [SHOP] odebrałem message', event.data, 'origin:', event.origin);
  const { type, vars } = event.data;
  if (type !== 'updateAll' || typeof vars !== 'object') return;

  // 1) Nadpisujemy :root inline
  Object.entries(vars).forEach(([name, value]) => {
    document.documentElement.style.setProperty(name, value, 'important');
  });

  // 2) Dokładamy globalny <style> z !important na * (po Bootstrapie)
  let style = document.getElementById('theme-overrides');
  if (!style) {
    style = document.createElement('style');
    style.id = 'theme-overrides';
    document.head.appendChild(style);
  }
  // budujemy treść * { --var: val !important }
  style.textContent = Object.entries(vars)
    .map(([name, value]) => `* { ${name}: ${value} !important; }`)
    .join('\n');
});
