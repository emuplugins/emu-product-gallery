<h1>Emu Product Gallery - Documentação de Uso</h1>

<p>Este plugin permite adicionar uma galeria de imagens para produtos em seu site WordPress utilizando o shortcode <code>[emu_product_gallery]</code>. A galeria pode ser personalizada através de diferentes atributos. Abaixo, explicamos o funcionamento de cada um desses atributos.</p>




<h2>Como usar:</h2>
<p><strong>Descrição:</strong> Quando o shortcode é usado sem atributos, ele exibe a galeria associada ao campo <code>emu_product_gallery_field</code> (campo padrão).</p>
<p><strong>Como usar:</strong> Se o shortcode for utilizado sem qualquer atributo, ele buscará o campo <code>emu_product_gallery_field</code> para gerar a galeria.</p>
<p><strong>Exemplo:</strong></p>
<pre><code>[emu_product_gallery]</code></pre>
<p><strong>Comportamento:</strong> Este é o comportamento padrão e exibe a galeria associada ao campo personalizado <code>emu_product_gallery_field</code>.</p>

<h2>Atributos do Shortcode</h2>

<h3>1. <code>thumbnail</code></h3>
<p><strong>Descrição:</strong> Exibe a imagem destacada (thumbnail) do produto.</p>
<p><strong>Como usar:</strong> Ao utilizar o atributo <code>thumbnail</code>, o shortcode irá exibir a imagem destacada do produto. Caso o produto não tenha uma imagem destacada, a galeria ficará vazia.</p>
<p><strong>Exemplo:</strong></p>
<pre><code>[emu_product_gallery thumbnail]</code></pre>
<p><strong>Comportamento:</strong> Este atributo apenas exibe a imagem principal do produto, geralmente definida no painel de edição do produto no WooCommerce.</p>

<h3>2. <code>woocommerce</code></h3>
<p><strong>Descrição:</strong> Exibe a galeria principal de imagens do WooCommerce do produto.</p>
<p><strong>Como usar:</strong> Ao usar o atributo <code>woocommerce</code>, o shortcode irá exibir todas as imagens vinculadas à galeria do produto no WooCommerce. Isso inclui todas as imagens adicionais configuradas para o produto.</p>
<p><strong>Exemplo:</strong></p>
<pre><code>[emu_product_gallery woocommerce]</code></pre>
<p><strong>Comportamento:</strong> Esse atributo carrega todas as imagens associadas ao produto, exibindo uma galeria completa com todas as imagens vinculadas ao produto na seção de imagens do WooCommerce.</p>

<h3>3. <code>field</code></h3>
<p><strong>Descrição:</strong> Exibe imagens ou URLs associadas a um campo personalizado (meta campo) específico do produto.</p>
<p><strong>Como usar:</strong> O atributo <code>field</code> permite que você defina um campo personalizado, cujo valor será usado para gerar a galeria. O campo pode conter URLs de imagens, vídeos ou outros tipos de mídia.</p>
<p><strong>Exemplo:</strong></p>
<pre><code>[emu_product_gallery field="campo_personalizado"]</code></pre>
<p><strong>Comportamento:</strong> Ao passar o nome de um campo personalizado como valor para o atributo <code>field</code>, o shortcode buscará as informações desse campo e as exibirá na galeria. O campo pode ser um campo de imagem ou qualquer outro tipo de mídia.</p>

<p>Com esses atributos, você pode personalizar a galeria de imagens de acordo com suas necessidades, seja exibindo a imagem destacada, as imagens da galeria do WooCommerce ou as imagens associadas a campos personalizados.</p>
