<script setup lang="ts">
/**
 * SeoJsonLd.vue
 * Injects a ProfessionalService JSON-LD schema into <head>.
 * Works in CSR (lifecycle hooks) and SSR (Inertia <Head>).
 */
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    appUrl?: string;
}>();

const baseUrl = computed(() => props.appUrl ?? 'https://horizonshot.pl');

const schema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'ProfessionalService',
    name: 'Łukasz Hil – HorizonShot',
    alternateName: 'HorizonShot',
    description:
        'Profesjonalne usługi dronowe, fotografia lotnicza, cinematografia z powietrza i montaż wideo. Bezpłatne konsultacje. Koszty dojazdu wliczone w cenę w obrębie Woj. Mazowieckiego.',
    url: baseUrl.value,
    logo: `${baseUrl.value}/images/logo.png`,
    image: `${baseUrl.value}/images/og-image.jpg`,
    telephone: null, // fill in when available
    priceRange: '$$',
    currenciesAccepted: 'PLN',
    paymentAccepted: 'Cash, Bank Transfer',
    areaServed: [
        {
            '@type': 'AdministrativeArea',
            name: 'Województwo Mazowieckie',
            alternateName: 'Masovian Voivodeship',
        },
        {
            '@type': 'City',
            name: 'Warszawa',
        },
    ],
    address: {
        '@type': 'PostalAddress',
        addressRegion: 'Mazowieckie',
        addressCountry: 'PL',
    },
    geo: {
        '@type': 'GeoCoordinates',
        latitude: 52.2297,
        longitude: 21.0122,
    },
    hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'Usługi Dronowe i Fotograficzne',
        itemListElement: [
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Filmowanie z drona',
                    description: 'Profesjonalne nagrania wideo z lotu ptaka.',
                },
            },
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Fotografia lotnicza',
                    description: 'Zdjęcia z drona nieruchomości, budów i wydarzeń.',
                },
            },
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Fotografia produktowa',
                    description: 'Profesjonalna fotografia produktów dla e-commerce i reklamy.',
                },
            },
            {
                '@type': 'Offer',
                itemOffered: {
                    '@type': 'Service',
                    name: 'Montaż wideo',
                    description: 'Profesjonalny montaż materiałów wideo i postprodukcja.',
                },
            },
        ],
    },
    sameAs: [],
}));

const schemaString = computed(() => JSON.stringify(schema.value));
</script>

<template>
    <!--
        Inertia <Head> hoists all child elements into the document <head>.
        Using v-text on a <script> tag avoids Vue escaping the JSON.
    -->
    <Head>
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-text="schemaString" />
    </Head>
</template>
