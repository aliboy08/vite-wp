<h1>Vite + WP</h1>

Clone the repo on your theme directory
```
gh repo clone aliboy08/vite-wp
```

Navigate to the directory and run install
```
npm install
```


<h3>Include the php file on your functions.php</h3>

```
include_once 'vite-wp/vite-wp.php';
```


<h3>Load asset on wp</h3>

```
FF\Vite\load_asset('src/main.js');

// only load the css, exclude js:
FF\Vite\load_asset('src/main.js', ['css_only' => true]);
```


<h3>Add entry points at vite.config.js</h3>

```
export default {
	plugins: [
		v4wp( {
			input: {
				critical: 'src/critical.js',
				main: 'src/main.js',
				// add your additional entry points here
				your_script: 'src/path_to_your_script.js',
			},
			outDir: 'dist',
		} ),
	],
}; 
```


<h4>Start development</h4>

```
npm run dev
```

<h4>Bundle for production</h4>
    
```
npm run build
```


<h3>Extra</h3>

<h4>Load critical css</h4>

prints the contents of the /src/css/critical.scss file as inline css on the head via wp_head action hook
```
FF\Vite\load_critical_css();
```

<h4>Defer css</h4>

adds defer attributes (link=preload) on css link tags, so it doesn't render block
```
FF\Vite\defer_css();
```
