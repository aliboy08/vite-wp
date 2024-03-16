<h1>Vite + WP boilerplate v2</h1>

Clone the repo on your theme directory
```
gh repo clone aliboy08/vite-wp-2
```

Run install
```
npm install
```


Include the php file on your functions.php
```
include_once 'vite-wp/vite-wp.php';
```

Load the asset on wp
```
FF\Vite\load_asset('src/main.js');
```

To start development
```
npm run dev
```

To bundle for production
```
npm run build
```

<h4>Utilities</h4>

<h5>Load critical css</h5>
inlines the critical css on the head, put your critical css codes at /vite-wp/src/css/critical.scss
```
FF\Vite\load_critical_css();
```

<h5>Defer css</h5>
adds defer attributes (link=preload) on css link tags, so it doesn't render block
```
FF\Vite\defer_css();
```
