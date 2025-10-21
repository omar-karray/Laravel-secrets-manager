---
title: Laravel Vault Suite
description: Vault/OpenBao integration toolkit for Laravel with first-class Artisan workflows.
---

![Laravel Vault Suite banner](assets/banner.svg)

<div class="hero">
  <div class="hero__content">
    <h1>Laravel Vault Suite</h1>
    <p>Command-first secrets management for Laravel backed by HashiCorp Vault and OpenBao. Unseal clusters, mount engines, fetch credentials, and wire secure configuration without touching <code>.env</code> files.</p>
    <p class="hero__cta">
      <a class="md-button md-button--primary" href="getting-started/">Get started</a>
      <a class="md-button" href="commands/">Command reference</a>
      <a class="md-button" href="tutorials/">Tutorials</a>
    </p>
  </div>
</div>

<section class="feature-grid">
  <article>
    <h2>Operational tooling</h2>
    <p>Purpose-built Artisan commands for unsealing clusters, mounting engines, listing secrets, and reading payloads—ready for SRE workflows and CI/CD automation.</p>
  </article>
  <article>
    <h2>Fluent PHP API</h2>
    <p>Inject the service or use the facade to fetch, write, list, and delete secrets directly from your Laravel code, with driver selection per call.</p>
  </article>
  <article>
    <h2>Multibackend driver system</h2>
    <p>HashiCorp Vault and OpenBao support out of the box, plus a contract for extending to other secret providers.</p>
  </article>
</section>

<section class="split">
  <div>
    <h2>Quick start</h2>
    <pre><code class="language-bash">composer require deepdigs/laravel-vault-suite
php artisan vendor:publish --tag="vault-suite-config"

# Check seal status
php artisan vault:status

# Mount a KV engine ready for app secrets
php artisan vault:enable-engine secret/apps --option=version=2

# List and read secrets
php artisan vault:list secret/apps
php artisan vault:read secret/apps/database --key=password
</code></pre>
  </div>
  <div>
    <h2>Learn by doing</h2>
    <ul>
      <li><a href="tutorials/#1-daily-command-workflow">Daily command workflow</a></li>
      <li><a href="tutorials/#2-loading-secrets-into-laravel-configuration">Load secrets into Laravel configuration</a></li>
      <li><a href="tutorials/#3-securing-database-credentials-with-vault">Secure database credentials with Vault</a></li>
    </ul>
  </div>
</section>

<section class="resources">
  <h2>Explore the docs</h2>
  <div class="card-grid">
    <div class="card">
      <h3>Getting Started</h3>
      <p>Installation paths, configuration publishing, and environment setup.</p>
      <a href="getting-started/">Start here →</a>
    </div>
    <div class="card">
      <h3>Commands</h3>
      <p>Detailed reference for <code>vault:status</code>, <code>vault:unseal</code>, <code>vault:list</code>, <code>vault:read</code>, and <code>vault:enable-engine</code>.</p>
      <a href="commands/">Read the command guide →</a>
    </div>
    <div class="card">
      <h3>Configuration</h3>
      <p>Understand every option in <code>config/vault-suite.php</code> including bootstrap and caching.</p>
      <a href="configuration/">Dive into configuration →</a>
    </div>
    <div class="card">
      <h3>Programmatic API</h3>
      <p>Use the service/facade to interact with vault drivers from PHP.</p>
      <a href="api/">Explore the API →</a>
    </div>
  </div>
</section>

<section class="cta">
  <h2>Ready to secure your secrets?</h2>
  <p>Install Laravel Vault Suite and start managing secrets from Artisan today.</p>
  <p>
    <a class="md-button md-button--primary" href="https://github.com/omar-karray/laravel-vault-suite">View on GitHub</a>
    <a class="md-button" href="https://packagist.org/packages/deepdigs/laravel-vault-suite">Install via Packagist</a>
  </p>
</section>

