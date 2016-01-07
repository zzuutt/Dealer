<?php
/**
* This class has been generated by TheliaStudio
* For more information, see https://github.com/thelia-modules/TheliaStudio
*/

namespace Dealer;

use Dealer\Model\DealerContactInfoQuery;
use Dealer\Model\DealerContactQuery;
use Dealer\Model\DealerContentQuery;
use Dealer\Model\DealerFolderQuery;
use Dealer\Model\DealerQuery;
use Dealer\Model\DealerShedulesQuery;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Module\BaseModule;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;

/**
 * Class Dealer
 * @package Dealer
 */
class Dealer extends BaseModule
{
    const MESSAGE_DOMAIN = "dealer";
    const ROUTER = "router.dealer";

    public function postActivation(ConnectionInterface $con = null)
    {
        try {
            DealerQuery::create()->findOne();
            DealerContactInfoQuery::create()->findOne();
            DealerContactQuery::create()->findOne();
            DealerShedulesQuery::create()->findOne();
            DealerContentQuery::create()->findOne();
            DealerFolderQuery::create()->findOne();
        } catch (\Exception $e) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);
        }
    }

    public function getHooks(){
        return [
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "dealer.extra.content.edit",
                "title" => "Dealer Extra Content",
                "description" => [
                    "en_US" =>"Allow you to insert element in modules tab on Dealer edit page",
                    "fr_FR" =>"Permet l'ajout de contenu sur la partie module de l'edition",
                ],
                "active" => true,
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "dealer.edit.js",
                "title" => "Dealer Extra Js",
                "description" => [
                    "en_US" =>"Allow you to insert js on Dealer edit page",
                    "fr_FR" =>"Permet l'ajout de js sur l'edition",
                ],
                "active" => true,
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "dealer.additional",
                "title" => "Dealer Extra Tab",
                "description" => [
                    "en_US" =>"Allow you to insert a tab on Dealer edit page",
                    "fr_FR" =>"Permet l'ajout d'une page sur l'edition d'un point de vente",
                ],
                "active" => true,
                "block" => true,
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "dealer.edit.nav.bar",
                "title" => "Dealer Edition NavBar Link",
                "description" => [
                    "en_US" =>"Allow you to insert link between arrow previous and next on edtion view",
                    "fr_FR" =>"Permet l'ajout d'un lien sur la page d'édition entre les liens suivant et précedent",
                ],
                "active" => true,
            ),
        ];
    }
}
