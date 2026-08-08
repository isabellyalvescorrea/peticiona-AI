  </main>

  <footer class="border-t border-gold/[0.1] px-5 py-6 sm:px-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-[12px] text-silver/70">
        © <?= date('Y') ?> <?= e(APP_NAME) ?> &middot; Ambiente reservado do advogado
      </p>
      <p class="text-[12px] text-silver/60">
        Protótipo de interface — dados fictícios, sem valor jurídico.
      </p>
    </div>
  </footer>
</div>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
