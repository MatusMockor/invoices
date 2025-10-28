# 🚀 Development Workflow

## 🔥 Watch Mode (ODPORÚČANÉ)

Pre automatický rebuild pri zmenách:

```bash
npm run watch
```

Alebo použij helper script:
```bash
./watch.sh
```

**Výhody:**
- ✅ Automatický rebuild pri uložení súboru
- ✅ Rýchlejšie ako Vite dev server
- ✅ Žiadne "preamble" chyby
- ✅ Funguje so všetkými komponentami

**Nevýhody:**
- ❌ Musíš manuálne refreshnúť prehliadač (F5)
- ❌ Žiadny Hot Module Replacement

---

## 🔄 Ako to funguje:

1. **Spusti watch mode:**
   ```bash
   npm run watch
   ```

2. **Otvor prehliadač:**
   ```
   http://localhost/
   ```

3. **Uprav súbor** (napr. `resources/js/pages/Dashboard.tsx`)

4. **Vite automaticky zbuilduje** (vidíš v termináli)

5. **Refresh prehliadač** (`Cmd/Ctrl + R`)

---

## 📦 Jednotlivý Build

Pre jednorazový build:
```bash
npm run build
```

---

## 🎯 Laravel Server

Uisti sa, že beží Laravel:

**Docker/Sail:**
```bash
./vendor/bin/sail up
```

**Alebo lokálne:**
```bash
php artisan serve
```

---

## 🛠️ Troubleshooting

### Problem: Zmeny sa neprejavujú
**Riešenie:** 
1. Hard refresh prehliadača: `Cmd + Shift + R` (Mac) / `Ctrl + Shift + R` (Win)
2. Vymaž cache: `rm -rf node_modules/.vite`
3. Rebuild: `npm run build`

### Problem: Watch nefunguje
**Riešenie:**
1. Zastav watch (`Ctrl + C`)
2. Vyčisti: `rm -rf public/build`
3. Spusti znova: `npm run watch`

### Problem: "Cannot GET /api/..."
**Riešenie:** Skontroluj, že Laravel server beží

---

## 📝 Workflow Summary

```
┌─────────────────┐
│  Edit Source    │  Uprav .tsx/.ts/.css
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Vite Watch     │  Automaticky zbuilduje
│  (npm run watch)│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Refresh Browser│  F5 alebo Cmd+R
│  http://localhost/│
└─────────────────┘
```

---

## 💡 Tipy

- **VS Code Extension:** Nainštaluj si "Live Server" alebo "Browser Preview" pre rýchlejší refresh
- **Browser Extension:** "Auto Refresh" pre automatický refresh pri zmene
- **Príkaz na pozadí:** `npm run watch &` (spustí watch na pozadí)

---

## 🎨 Štruktúra pre development

```
resources/js/
├── components/     # UI komponenty
├── pages/         # Stránky aplikácie
├── services/      # API volania
├── hooks/         # Custom React hooks
├── contexts/      # React contexts
└── lib/           # Utilities
```

Každá zmena v týchto súboroch spustí automatický rebuild! 🔄

