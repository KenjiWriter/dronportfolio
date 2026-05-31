import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'HorizonShot';

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            // Title is managed per-page via Inertia <Head>; this is a fallback.
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) =>
                resolvePageComponent(
                    `./pages/${name}.vue`,
                    import.meta.glob<DefineComponent>('./pages/**/*.vue'),
                ),
            setup: ({ App, props, plugin }) => {
                const Ziggy = (props.initialPage.props as any).ziggy;
                return createSSRApp({ render: () => h(App, props) })
                    .use(plugin)
                    .use(ZiggyVue, Ziggy);
            },
        }),
    { cluster: true },
);
