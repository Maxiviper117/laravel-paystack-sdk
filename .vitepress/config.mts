import { defineConfig } from 'vitepress';

export default defineConfig({
  title: 'Laravel Paystack SDK',
  description: 'Laravel-native Paystack SDK built on Saloon with an Actions-first, DTO-first public API.',
  srcDir: 'docs',
  base: '/laravel-paystack-sdk/',
  lastUpdated: true,
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Guide', link: '/getting-started' },
      {
        text: 'Resources',
        items: [
          { text: 'Transactions', link: '/transactions' },
          { text: 'Customers', link: '/customers' },
          { text: 'Plans', link: '/plans' },
          { text: 'Subscriptions', link: '/subscriptions' },
          { text: 'Webhooks', link: '/webhooks' },
          { text: 'Support Matrix', link: '/support-matrix' },
        ],
      },
      { text: 'GitHub', link: 'https://github.com/Maxiviper117/laravel-paystack-sdk' },
    ],
    search: {
      provider: 'local',
    },
    sidebar: {
      '/': [
        {
          text: 'Start Here',
          items: [
            { text: 'Overview', link: '/' },
            { text: 'Getting Started', link: '/getting-started' },
            { text: 'Installation', link: '/installation' },
            { text: 'Configuration', link: '/configuration' },
          ],
        },
        {
          text: 'Payments',
          items: [
            { text: 'Transactions', link: '/transactions' },
            { text: 'Customers', link: '/customers' },
          ],
        },
        {
          text: 'Billing',
          items: [
            { text: 'Plans', link: '/plans' },
            { text: 'Subscriptions', link: '/subscriptions' },
          ],
        },
        {
          text: 'Platform',
          items: [
            { text: 'Webhooks', link: '/webhooks' },
            { text: 'Support Matrix', link: '/support-matrix' },
          ],
        },
      ],
    },
    socialLinks: [
      { icon: 'github', link: 'https://github.com/Maxiviper117/laravel-paystack-sdk' },
    ],
    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © David',
    },
  },
});
