# CRM Vue Components - Route Props Usage

Tento dokument popisuje, ako používať route props vo Vue komponentoch namiesto hardcoded endpointov.

## Koncept

Namiesto hardcoded URL endpointov vo Vue komponentoch, predávame route ako props z Blade template. Toto riešenie poskytuje:

- **Flexibilitu**: Route sa môžu meniť bez úpravy Vue komponentov
- **Bezpečnosť**: CSRF token sa predáva ako prop
- **Testovateľnosť**: Komponenty sú ľahšie testovateľné
- **Lepšiu architektúru**: Oddelenie frontend a backend logiky

## Použitie v Blade Template

```blade
<contacts-index 
    :routes="{
        index: '{{ route('crm.contacts.index') }}',
        store: '{{ route('crm.contacts.store') }}',
        show: '{{ route('crm.contacts.show', ':id') }}',
        update: '{{ route('crm.contacts.update', ':id') }}',
        destroy: '{{ route('crm.contacts.destroy', ':id') }}',
        bulkDelete: '{{ route('crm.contacts.bulk-delete') }}',
        bulkUpdate: '{{ route('crm.contacts.bulk-update') }}',
        export: '{{ route('crm.contacts.export') }}',
        import: '{{ route('crm.contacts.import') }}'
    }"
    :tags-route="'{{ route('api.crm.tags.index') }}'"
    :csrf-token="'{{ csrf_token() }}'"
></contacts-index>
```

## Vue Komponenta s Props

```vue
<template>
  <!-- Template content -->
</template>

<script>
export default {
  name: 'ContactsIndex',
  props: {
    routes: {
      type: Object,
      required: true
    },
    tagsRoute: {
      type: String,
      required: true
    },
    csrfToken: {
      type: String,
      required: true
    }
  },
  methods: {
    async fetchContacts() {
      const response = await fetch(`${this.routes.index}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      // ...
    },
    
    async deleteContact(contact) {
      const url = this.routes.destroy.replace(':id', contact.id)
      const response = await fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': this.csrfToken
        }
      })
      // ...
    }
  }
}
</script>
```

## Predávanie Props do Child Komponentov

```vue
<template>
  <div>
    <ContactCreateModal 
      :routes="routes"
      :csrf-token="csrfToken"
      @close="showCreateModal = false" 
      @created="contactCreated" 
    />
  </div>
</template>

<script>
export default {
  props: {
    routes: {
      type: Object,
      required: true
    },
    csrfToken: {
      type: String,
      required: true
    }
  }
}
</script>
```

## Výhody tohto prístupu

1. **Žiadne hardcoded URL**: Všetky endpointy sa predávajú ako props
2. **Lepšia testovateľnosť**: Props sa dajú ľahko mockovať v testoch
3. **Flexibilita**: Route sa môžu meniť bez úpravy Vue kódu
4. **Bezpečnosť**: CSRF token sa predáva bezpečne
5. **Čistý kód**: Oddelenie frontend a backend logiky

## Príklady použitia

### Základné CRUD operácie

```javascript
// Create
const response = await fetch(this.routes.store, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': this.csrfToken
  },
  body: JSON.stringify(data)
})

// Read
const response = await fetch(this.routes.index)

// Update
const url = this.routes.update.replace(':id', id)
const response = await fetch(url, {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': this.csrfToken
  },
  body: JSON.stringify(data)
})

// Delete
const url = this.routes.destroy.replace(':id', id)
const response = await fetch(url, {
  method: 'DELETE',
  headers: {
    'X-CSRF-TOKEN': this.csrfToken
  }
})
```

### Bulk operácie

```javascript
// Bulk delete
const response = await fetch(this.routes.bulkDelete, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': this.csrfToken
  },
  body: JSON.stringify({ contact_ids: selectedIds })
})
```

## Best Practices

1. **Vždy definujte props ako required**: Zabezpečí to, že komponenta dostane všetky potrebné dáta
2. **Použite TypeScript**: Pre lepšiu type safety
3. **Validujte props**: Použite prop validation vo Vue
4. **Dokumentujte props**: Popíšte, čo každý prop obsahuje
5. **Testujte komponenty**: S mock props pre lepšiu testovateľnosť

## Migrácia z hardcoded endpointov

1. Identifikujte všetky hardcoded URL v komponente
2. Pridajte props pre route a CSRF token
3. Nahraďte hardcoded URL použitím props
4. Aktualizujte Blade template, aby predával props
5. Otestujte funkcionalitu

Tento prístup zaisťuje lepšiu architektúru a udržateľnosť Vue komponentov v Laravel aplikácii.
