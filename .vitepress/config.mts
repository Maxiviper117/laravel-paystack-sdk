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
      { text: 'Examples', link: '/examples/' },
      {
        text: 'Resources',
        items: [
          { text: 'Transactions', link: '/transactions' },
          { text: 'Customers', link: '/customers' },
          { text: 'Disputes', link: '/disputes' },
          { text: 'Refunds', link: '/refunds' },
          { text: 'Billing Layer', link: '/billing-layer' },
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
          collapsed: false,
          items: [
            { text: 'Overview', link: '/' },
            { text: 'Getting Started', link: '/getting-started' },
            { text: 'Installation', link: '/installation' },
            { text: 'Configuration', link: '/configuration' },
          ],
        },
        {
          text: 'Examples',
          collapsed: false,
          items: [
            { text: 'Examples Overview', link: '/examples/' },
            { text: 'One-Time Checkout', link: '/examples/checkout' },
            { text: 'Verify a Transaction', link: '/examples/verify-transaction' },
            { text: 'Manage Customers', link: '/examples/customers' },
            { text: 'Optional Billing Layer', link: '/examples/billing-layer' },
            { text: 'Subscription Billing Flow', link: '/examples/subscriptions' },
            { text: 'Webhook Processing', link: '/examples/webhooks' },
            { text: 'Manager and Facade Usage', link: '/examples/manager-and-facade' },
          ],
        },
        {
          text: 'Laravel Patterns',
          collapsed: false,
          items: [
            { text: 'API Resources', link: '/examples/api-resources' },
            { text: 'Artisan Commands', link: '/examples/artisan-commands' },
            { text: 'Attribute Casting', link: '/examples/attribute-casting' },
            { text: 'Blade Components', link: '/examples/blade-components' },
            { text: 'Broadcasting and Events', link: '/examples/broadcasting' },
            { text: 'Caching Paystack Data', link: '/examples/caching' },
            { text: 'Custom Validation Rules', link: '/examples/custom-validation-rules' },
            { text: 'Database Transactions', link: '/examples/database-transactions' },
            { text: 'Eloquent Observers', link: '/examples/eloquent-observers' },
            { text: 'Error Handling', link: '/examples/error-handling' },
            { text: 'Event Listeners', link: '/examples/event-listeners' },
            { text: 'Export and Reports', link: '/examples/export-reports' },
            { text: 'Form Request Validation', link: '/examples/form-requests' },
            { text: 'Middleware', link: '/examples/middleware' },
            { text: 'Payment Notifications', link: '/examples/notifications' },
            { text: 'Policies and Authorization', link: '/examples/policies' },
            { text: 'Query Scopes', link: '/examples/query-scopes' },
            { text: 'Queued Jobs', link: '/examples/queued-jobs' },
            { text: 'Scheduled Tasks', link: '/examples/scheduled-tasks' },
            { text: 'Service Container Binding', link: '/examples/service-container' },
            { text: 'Testing Paystack Integrations', link: '/examples/testing' },
          ],
        },
        {
          text: 'Payments',
          collapsed: true,
          items: [
            { text: 'Transactions', link: '/transactions' },
            { text: 'Customers', link: '/customers' },
            { text: 'Disputes', link: '/disputes' },
            { text: 'Refunds', link: '/refunds' },
          ],
        },
        {
          text: 'Billing',
          collapsed: true,
          items: [
            { text: 'Billing Layer', link: '/billing-layer' },
            { text: 'Plans', link: '/plans' },
            { text: 'Subscriptions', link: '/subscriptions' },
          ],
        },
        {
          text: 'Platform',
          collapsed: true,
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
