# richuploader-bundle

## Installation
### Step 1 - Install required composer modules

```
composer require nacholibre/richuploader-bundle
composer require liip/imagine-bundle
```
`liip/imagine-bundle` is used to create the thumbnails of uploaded images.

### Step 1.1 - Assets
Make sure you have jquery and jqueryui added to the page where you want to use the uploader.

### Step 2 - Add modules in AppKernel.php

```
<?php
// app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Liip\ImagineBundle\LiipImagineBundle(),
            new nacholibre\RichUploaderBundle\nacholibreRichUploaderBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3 - Register bundle routing
```
# app/config/routing.yml

nacholibre.rich_uploader:
    resource: "@nacholibreRichUploaderBundle/Controller/"
    type:     annotation
```

### Step 4 - Configure your mappings
```
# app/config/config.yml

nacholibre_rich_uploader:
    mappings:
        default:
            uri_prefix:         /uploads/richuploader
            upload_destination: %kernel.root_dir%/../web/uploads/richuploader/
            #mime_types: ['image/*']
            max_size: 5M
```

### Step 5 - Create your Entity
You need to extend `RichFile` entity.

```
<?php
// src/AppBundle/Entity/RichFile.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use nacholibre\RichUploaderBundle\Model\RichFile as nacholibreRichFile;
use nacholibre\RichUploaderBundle\Annotation\RichUploader;

/**
 * @ORM\Entity
 * @ORM\Table(name="images")
 * @RichUploader(config="default")
 */
class RichFile extends nacholibreRichFile {
}
```
and run `php app/console doctrine:schema:update --force --dump-sql` or `php bin/console ..` if you are using symfony > 2.8 version.

### Step 6 - Create doctrine associations
Let's say you have product entity, and you want to be able to add images in it.

```
<?php
// src/AppBundle/Entity/Product.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product")
 */
class Product {
    //..
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\RichFile")
     * @ORM\JoinTable(name="product_images",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true)}
     *      )
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $images;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\RichFile")
     */
    private $photo;
    
    //..
}
```

`images` is example of multiple images/files uploader and `photo` is example of single image/file uploader.

### Step 7 - Add the RichUploaderType to the your form type

```
<?php
// src/AppBundle/Form/ProductType.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('images', 'nacholibre\RichUploaderBundle\Form\Type\RichUploaderType', [
                'entity_class' => 'AppBundle\Entity\RichFile',
                'required' => true, 
                'multiple' => true,
                'size' => 'xs', //available options md and xs
            ])
            ->add('photo', 'nacholibre\RichUploaderBundle\Form\Type\RichUploaderType', [
                'entity_class' => 'AppBundle\Entity\RichFile',
                'required' => true,
                'multiple' => false, //false for single files and true for multiple
                'size' => 'xs', //available options md and xs
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Product'
        ));
    }
}
```
