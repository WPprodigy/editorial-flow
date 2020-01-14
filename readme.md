# Editorial Flow

Like Edit Flow, but clearly better 😜.

Disclaimer: This is a test project, not ready for use on production sites.

## Module: Editorial Comments

Editorial comments help you cut down on email overload and keep the conversation close to where it matters: your content. Threaded commenting in the admin, similar to what you find at the end of a blog post, allows writers and editors to privately leave feedback and discuss what needs to be changed before publication.

To utilize this module, head over to your nearest (testing) WordPress site and install/activate the plugin. Then in the block editor for a new post, head over to the sidebar's plugin list and select "Editorial Flow". View a quick [demo video](https://d.pr/v/CU0hrR).

![](https://d.pr/i/5R7zpS+)


## Extensibility: Adding Custom Modules

Adding a custom module:

```php
class My_Custom_Module extends Editorial_Flow\Modules() {};

// Register the module.
$module = new My_Custom_Module();
Editorial_Flow()->register_module( $module );
```

Quickly adding a panel to the EF sidebar in the Block Editor:

```javascript
var el = wp.element.createElement;

wp.hooks.addFilter( 'editorialFlowSidebarContent', 'efNotificationsPanel', function( content ) {
	content.push( NotificationsPanel() );
	return content;
}, 20 );

function NotificationsPanel() {
	return el( wp.components.Panel, {},
		el( wp.components.PanelBody, {
				title: 'Notifications',
				icon: 'megaphone',
				initialOpen: false
			},
			el( wp.components.PanelRow, {}, 'Example Content' )
		)
	);
}
```

## Contributing

#### Editing JavaScript files:

1) `npm install`
2) `npm run dev` to watch for changes and rebuild automatically.
3) `npm run build` to compile your changes into the minified files ready for release.


#### Running PHPUnit Tests:

1) Install PHPUnit: https://phpunit.de/getting-started/phpunit-7.html
2) Intialize the testing environment.

Example:

```
cd path/to/editorial-flow/
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

3) Run `phpunit`
