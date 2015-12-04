<?php
use Concrete\Core\Express\ObjectBuilder;

require_once __DIR__ . "/ObjectBuilderTestTrait.php";

class FormRendererTest extends PHPUnit_Framework_TestCase
{

    use \ObjectBuilderTestTrait;

    protected function getMockEntityManager()
    {
        $entityRepository = $this
            ->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $entityRepository->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue(array()));


        // Last, mock the EntityManager to return the mock of the repository
        $entityManager = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function($args) use ($entityRepository) {
                if ($args == '\Concrete\Express\Teacher') {
                    return $entityRepository;
                }
            }));


        return $entityManager;
    }

    public function testRenderForm()
    {
        $builder = $this->getObjectBuilder();
        /**
         * @var $student \Concrete\Core\Entity\Express\Entity
         */
        $student = $builder->buildObject();
        $form = new \Concrete\Core\Entity\Express\Form();

        // create field sets
        $fieldSet1 = new \Concrete\Core\Entity\Express\FieldSet();
        $fieldSet2 = new \Concrete\Core\Entity\Express\FieldSet();

        // Create our form controls
        $name = new \Concrete\Core\Entity\Express\Control\EntityNameControl();
        $explanation = new \Concrete\Core\Entity\Express\Control\TextControl();
        $explanation->setText('This is an explanation');

        // Create association
        $teacher = new \Concrete\Core\Entity\Express\Entity();
        $teacher->setName('Teacher');
        $associationBuilder = Core::make('express.builder.association');
        $associationBuilder->addOneToMany($student, $teacher);

        $studentAssociations = $student->getAssociations();
        $associationControl = new \Concrete\Core\Entity\Express\Control\AssociationControl();
        $associationControl->setAssociation($studentAssociations[0]);

        $fieldSet1->getControls()->add($name);
        $fieldSet1->getControls()->add($explanation);
        $fieldSet1->getControls()->add($associationControl);

        // Attribute controls
        foreach($student->getAttributes() as $attribute) {
            $attributeControl = new \Concrete\Core\Entity\Express\Control\AttributeKeyControl();
            $attributeControl->setAttributeKey($attribute);
            $fieldSet2->getControls()->add($attributeControl);
        }

        // Add field set to form
        $form->getFieldSets()->add($fieldSet1);
        $form->getFieldSets()->add($fieldSet2);
        $form->setEntity($student);

        // Render the form
        //$environment = $this->getMock('\Concrete\Core\Foundation\Environment');
        $renderer = Core::make('Concrete\Core\Express\Form\Renderer', array(Core::make('app'), $this->getMockEntityManager()));
        $html = $renderer->render($form);

        print Core::make('helper/text')->formatXML($html);
    }

}