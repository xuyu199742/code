<?php

namespace App\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Dingo\Api\Routing\Router;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Foundation\Console\RouteListCommand;

class PermissionCommand extends RouteListCommand
{
    /**
     * Dingo router instance.
     *
     * @var \Dingo\Api\Routing\Router
     */
    protected $router;

    /**
     * Array of route collections.
     *
     * @var array
     */
    protected $routes;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permission:list';
    //protected $signature = 'permission:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '列出所有权限节点';

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Guard', 'Title', 'Name', 'Group', 'Version'];


    /**
     * Create a new routes command instance.
     *
     * @param \Dingo\Api\Routing\Router $router
     *
     * @return void
     */
    public function __construct(Router $router)
    {

        // Ugly, but we need to bypass the constructor and directly target the
        // constructor on the command class.
        Command::__construct();

        $this->router = $router;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->routes = $this->router->getRoutes();

        parent::fire();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->routes = $this->router->getRoutes();
        parent::handle();
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = [];
        foreach ($this->router->getRoutes() as $collection) {
            foreach ($collection->getRoutes() as $route) {
                list($guard, $title, $group, $version) = $route->getAction()['permission'] ?? '';
                if ($guard && $title && $group && $version) {
                    if(in_array('admin',$route->getMiddleware())){
                        $routes[] = $this->filterRoute([
                            'guard_name'   => $guard,
                            'title'   => $title,
                            'name'    => $route->getName(),
                            'group'   => $group,
                            'version' => $version
                        ]);
                    }
                }
            }
        }
        $routes = Arr::sort($routes, function ($value)  {
            return $value['version'];
        });
        $names=[];
        foreach ($routes as $route){
            unset($route['version']);
            $name=$route['name'];
            $names[]=$name;
            unset($route['name']);
            Permission::updateOrCreate(['name'=>$name],$route);
        }
        $has_name=Permission::pluck('name')->all();
        $delete=array_diff($has_name,$names);
        if(count($delete)>0){
            Permission::whereIn('name',$delete)->delete();
        }
    }

    /**
     * Display the routes rate limiting requests per second. This takes the limit
     * and divides it by the expiration time in seconds to give you a rough
     * idea of how many requests you'd be able to fire off per second
     * on the route.
     *
     * @param \Dingo\Api\Routing\Route $route
     *
     * @return null|string
     */
    protected function routeRateLimit($route)
    {
        list($limit, $expires) = [$route->getRateLimit(), $route->getRateLimitExpiration()];

        if ($limit && $expires) {
            return sprintf('%s req/s', round($limit / ($expires * 60), 2));
        }
    }

    /**
     * Filter the route by URI, Version, Scopes and / or name.
     *
     * @param array $route
     *
     * @return array|null
     */
    protected function filterRoute(array $route)
    {
        $filters = ['name', 'path', 'protected', 'unprotected', 'versions', 'scopes'];

        foreach ($filters as $filter) {
            if ($this->option($filter) && !$this->{'filterBy' . ucfirst($filter)}($route)) {
                return;
            }
        }

        return $route;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        foreach ($options as $key => $option) {
            if ($option[0] == 'sort') {
                unset($options[$key]);
            }
        }

        return array_merge(
            $options,
            [
                ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action) to sort by'],
                ['versions', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Filter the routes by version'],
                ['scopes', 'S', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Filter the routes by scopes'],
                ['protected', null, InputOption::VALUE_NONE, 'Filter the protected routes'],
                ['unprotected', null, InputOption::VALUE_NONE, 'Filter the unprotected routes'],
                ['short', null, InputOption::VALUE_NONE, 'Get an abridged version of the routes'],
            ]
        );
    }

    /**
     * Filter the route by its path.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByPath(array $route)
    {
        return Str::contains($route['uri'], $this->option('path'));
    }

    /**
     * Filter the route by whether or not it is protected.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByProtected(array $route)
    {
        return $this->option('protected') && $route['protected'] == 'Yes';
    }

    /**
     * Filter the route by whether or not it is unprotected.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByUnprotected(array $route)
    {
        return $this->option('unprotected') && $route['protected'] == 'No';
    }

    /**
     * Filter the route by its versions.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByVersions(array $route)
    {
        foreach ($this->option('versions') as $version) {
            if (Str::contains($route['versions'], $version)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter the route by its name.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByName(array $route)
    {
        return Str::contains($route['name'], $this->option('name'));
    }

    /**
     * Filter the route by its scopes.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByScopes(array $route)
    {
        foreach ($this->option('scopes') as $scope) {
            if (Str::contains($route['scopes'], $scope)) {
                return true;
            }
        }

        return false;
    }
}
