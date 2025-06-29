import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { App } from './app/app';

if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
  document.documentElement.setAttribute('data-bs-theme', 'dark');
} else {
  document.documentElement.setAttribute('data-bs-theme', 'light');
}

bootstrapApplication(App, appConfig)
  .catch((err) => console.error(err));
