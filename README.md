<h1>Emu Product Gallery - Usage Documentation</h1>

<p>This plugin allows you to add an image gallery for products on your WordPress site using the shortcode <code>[emu_product_gallery]</code>. The gallery can be customized through different attributes. Below, we explain how each of these attributes works.</p>

<h2>How to Use:</h2>
<p><strong>Description:</strong> When the shortcode is used without attributes, it displays the gallery associated with the <code>emu_product_gallery_field</code> field (default field).</p>
<p><strong>How to Use:</strong> If the shortcode is used without any attributes, it will fetch the <code>emu_product_gallery_field</code> field to generate the gallery.</p>
<p><strong>Example:</strong></p>
<pre><code>[emu_product_gallery]</code></pre>
<p><strong>Behavior:</strong> This is the default behavior and displays the gallery associated with the custom field <code>emu_product_gallery_field</code>.</p>

<h2>Shortcode Attributes</h2>

<h3>1. <code>thumbnail</code></h3>
<p><strong>Description:</strong> Displays the product’s featured image (thumbnail).</p>
<p><strong>How to Use:</strong> When using the <code>thumbnail</code> attribute, the shortcode will display the product’s featured image. If the product does not have a featured image, the gallery will be empty.</p>
<p><strong>Example:</strong></p>
<pre><code>[emu_product_gallery thumbnail]</code></pre>
<p><strong>Behavior:</strong> This attribute only displays the product’s main image, usually set in the product edit panel in WooCommerce.</p>

<h3>2. <code>woocommerce</code></h3>
<p><strong>Description:</strong> Displays the product’s main WooCommerce image gallery.</p>
<p><strong>How to Use:</strong> When using the <code>woocommerce</code> attribute, the shortcode will display all images linked to the WooCommerce product gallery. This includes all additional images configured for the product.</p>
<p><strong>Example:</strong></p>
<pre><code>[emu_product_gallery woocommerce]</code></pre>
<p><strong>Behavior:</strong> This attribute loads all images associated with the product, displaying a complete gallery with all images linked to the product in the WooCommerce image section.</p>

<h3>3. <code>field</code></h3>
<p><strong>Description:</strong> Displays images or URLs associated with a specific custom field (meta field) of the product.</p>
<p><strong>How to Use:</strong> The <code>field</code> attribute allows you to define a custom field whose value will be used to generate the gallery. The field can contain image URLs, videos, or other types of media.</p>
<p><strong>Example:</strong></p>
<pre><code>[emu_product_gallery field="custom_field"]</code></pre>
<p><strong>Behavior:</strong> By passing the name of a custom field as the value for the <code>field</code> attribute, the shortcode will retrieve the information from that field and display it in the gallery. The field can be an image field or any other type of media.</p>

<p>With these attributes, you can customize the image gallery according to your needs, whether displaying the featured image, the WooCommerce gallery images, or images associated with custom fields.</p>
