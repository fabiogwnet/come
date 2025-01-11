<?php
declare(strict_types=1);

namespace App\Security;

use App\Models\Brand;
use App\Models\CLI_Login;
use App\Models\CLI_Store;
use App\Models\CLI_StoreHasModule;
use App\Models\TB_GiverAdmin;
use Illuminate\Database\Eloquent\Collection;

class Scope
{
    public const SCOPE_TYPE_BRAND = 1;
    public const SCOPE_TYPE_STORE_GROUP = 2;
    public const SCOPE_TYPE_STORE = 3;

    /**
     * @var Brand
     */
    private $brand = null;

    /**
     * @var LoginInterface
     */
    private $login = null;

    /**
     * @var array
     */
    private $scope = [];

    /**
     * @var Collection|null
     */
    private $stores = null;

    /**
     * Scope constructor.
     * @param Brand $brand
     * @param LoginInterface $login
     */
    public function __construct(Brand $brand, LoginInterface $login)
    {
        $this->brand = $brand;
        $this->login = $login;

        $this->verifyScope();
    }

    /**
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }


    /**
     * @return LoginInterface
     */
    public function getLogin(): LoginInterface
    {
        return $this->login;
    }

    /**
     * @param array $addScope
     * @return $this
     */
    public function addScopeParameter(array $addScope): self
    {
        $this->scope = array_merge($this->scope, $addScope);
        return $this;
    }

    /**
     * @return array
     */
    public function getScope(): array
    {
        return $this->scope;
    }

    /**
     * @return void
     */
    public function verifyScope(): void
    {
        $this->scope = $this->login->getScopeParams();
    }

    /**
     * @param bool $listDefault
     *
     * @return Collection|null
     */
    public function getStores(array $statusIds = [], bool $listDefault = true, bool $turnList = false): ?Collection
    {
        if (!is_null($this->stores) and count($statusIds) == 0) {
            return $this->stores;
        }

        if ($this->scope) {
            switch ($this->scope['name']) {
                case 'store':
                    $query = CLI_Store::query()
                        ->where('brand_id', '=', $this->getBrand()->id)
                        ->where('id', '=', $this->scope['id']);

                    if (count($statusIds)) {
                        $query->whereIn('status_id', $statusIds);
                    }

                    if ($turnList) {
                        $queryHasModuleSales = CLI_StoreHasModule::selectRaw("1")
                            ->byBrandId($this->getBrand()->id)
                            ->whereRaw("cli_store_has_module.store_id = cli_store.id")
                            ->where("cli_store_has_module.module_id", 29)
                            ->limit(1);

                        $queryHasModuleDesktop = CLI_StoreHasModule::selectRaw("1")
                            ->byBrandId($this->getBrand()->id)
                            ->whereRaw("cli_store_has_module.store_id = cli_store.id")
                            ->where("cli_store_has_module.module_id", 31)
                            ->limit(1);

                        $query
                            ->selectSub($queryHasModuleSales, 'has_module_sales')
                            ->selectSub($queryHasModuleDesktop, 'has_module_desktop')
                            ->selectRaw('cli_store.id as id');

                        $query->having('has_module_sales', '>', 0);
                    }

                    $this->stores = $query->cache()->get();
                    break;
                case 'store_group':
                case 'brand_store_group':
                    $this->stores = CLI_Store::storesByGroup(
                        $this->getScope(),
                        $this->getBrand()->id,
                        $statusIds,
                        $turnList
                    );

                    break;
                default:
                    if ($listDefault) {
                        $this->stores = CLI_Store::getListDefaultScope(
                            $this->getScope(),
                            $this->getBrand()->id,
                            $statusIds,
                            $turnList
                        );
                    }

                    break;
            }

            return $this->stores;
        }

        if ($listDefault) {
            $this->stores = CLI_Store::getListDefaultScope($this->getScope(), $this->getBrand()->id, $statusIds);
        }

        return $this->stores;
    }

    /**
     * @return array
     */
    public function getStoreIds(array $statusIds = [], bool $listDefault = true): array
    {
        $request = app('request');
        $storeArray = $this->getStores($statusIds, $listDefault)->pluck('id')->toArray();

        if ($this->isGeneralScope() || $this->isGiverAdmin()) {
            $identityId = $request->header('I-ID', null);
            if (is_null($identityId)) {
                $storeArray = array_merge($storeArray, [0]);
            }
        }

        return $storeArray;
    }

    /**
     * @return array
     */
    public function getStoreIdsActives(): array
    {
        //Colocado CLI_Store::STATUS_CHARGING pois as querys do cli considera is_active = '1' and payment_pending = '0'
        return $this->getStoreIds([CLI_Store::STATUS_ACTIVE, CLI_Store::STATUS_INACTIVE, CLI_Store::STATUS_CHARGING]);
    }

    /**
     * @return bool
     */
    public function isGeneralScope(): bool
    {
        if ($this->scope['name'] === 'general' && $this->scope['type_id'] === self::SCOPE_TYPE_BRAND) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isStoreScope(): bool
    {
        if ($this->scope['name'] === 'store' && $this->scope['type_id'] === self::SCOPE_TYPE_STORE) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isStoreGroupScope(): bool
    {
        if ($this->scope['name'] === 'store_group' && $this->scope['type_id'] === self::SCOPE_TYPE_STORE_GROUP) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isBrandStoreGroupScope(): bool
    {
        if ($this->scope['name'] === 'brand_store_group' && $this->scope['type_id'] === self::SCOPE_TYPE_BRAND) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isGiverAdmin(): bool
    {
        if ($this->login instanceof TB_GiverAdmin) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        if ($this->login instanceof CLI_Login and $this->login->is_super_admin) {
            return true;
        }

        return false;
    }

    public function getStoreIdsTurnList(array $statusIds = [], bool $listDefault = true): array
    {
        return $this->getStores($statusIds, $listDefault, true)->pluck('id')->toArray();
    }
}
