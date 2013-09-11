<?php

namespace Sifter;

use Sifter\Resource\Resource;

class Issues
{

    private $id;
    private $params = array();

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function get($params = array())
    {
        $endpoint = "/api/projects/" . $this->id . "/issues";
        $issues = Request::make($endpoint, $this->params + $params);

        $allIssues = $issues['issues'];

        if ($issues['total_pages'] > 1)
        {
            $current_page = 2;

            for($i = $current_page; $i <= $issues['total_pages']; $i++)
            {
                $issues = Request::make($endpoint, $this->params + $params + array('page' => $i, 'per_page' => 100));
                $allIssues = array_merge($allIssues, $issues['issues']);
            }
        }

        $this->params = array();

        return Resource::make($allIssues);
    }

    public function __call($name, $args)
    {
        $statuses = array(
            'open' => 1, 'reopened' => 2,
            'resolved' => 3, 'closed' => 4
        );
        $priorities = array(
            'critical' => 1, 'high' => 2, 'normal' => 3,
            'low' => 4, 'trivial' => 5
        );
        if (in_array($name, array_keys($statuses))) {
            $status = $statuses[$name];
            if (isset($this->params['s'])) {
                $status = $this->params['s'] . '-' . $status;
            }
            $this->params['s'] = $status;
        } elseif (in_array($name, array_keys($priorities))) {
            $priority = $priorities[$name];
            if (isset($this->params['p'])) {
                $priority = $this->params['p'] . '-' . $priority;
            }
            $this->params['p'] = $priority;
        } elseif ($name === 'search') {
            $this->params['q'] = $args[0];
        } else {
            $this->params[$name] = $args[0];
        }
        return $this;
    }

}
