<?php


namespace Kanboard\Core;


/**
 * TwigHelper
 *
 * @property \Kanboard\Core\Template  $template
 * @property                          $task
 * @property                          $project
 * @property boolean                  $not_editable
 * @property                          $board_highlight_period
 */
class TwigHelper
{
    public function __construct($template, $task, $project, $not_editable, $board_highlight_period)
    {
        $this->template = $template;
        $this->task = $task;
        $this->project = $project;
        $this->not_editable = $not_editable;
        $this->board_highlight_period = $board_highlight_period;
    }

    public function board_is_collapsed() {
        return $this->template->board->isCollapsed($this->task['project_id']);
    }

    protected function task_hook($position)
    {
        $visibility = $this->not_editable ? 'public' : 'private';
        return $this->template->hook->render("template:board:$visibility:task:$position-title", array('task' => $this->task));
    }

    public function task_hook_before()
    {
        return $this->task_hook('before');
    }

    public function task_hook_after()
    {
        return $this->task_hook('after');
    }

    public function title() {
        if ($this->not_editable) {
            return $this->template->url->link(
                $this->template->text->e($this->task['title']),
                'TaskViewController',
                'readonly',
                array('task_id' => $this->task['id'], 'token' => $this->project['token'])
            );
        } elseif ($this->board_is_collapsed()) {
            return $this->template->url->link(
                $this->template->text->e($this->task['title']),
                'TaskViewController',
                'show',
                array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id']),
                false,
                '',
                $this->template->text->e($this->task['title'])
            );
        }

        return $this->template->url->link(
            $this->template->text->e($this->task['title']),
            'TaskViewController',
            'show',
            array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id'])
        );
    }

    public function dropdown() {
        return $this->template->render('task/dropdown', array('task' => $this->task, 'redirect' => 'board'));
    }

    public function edit_task() {
        return $this->template->modal->large('edit', '', 'TaskModificationController', 'edit', array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id']));
    }

    public function recently_modified() {
        return $this->task['date_modification'] > time() - $this->board_highlight_period;
    }

    public function view_href() {
        if ($this->not_editable) {
            return $this->template->url->href(
                'TaskViewController',
                'readonly',
                array(
                    'task_id' => $this->task['id'],
                    'token' => $this->project['token']
                )
            );
        } else {
            return $this->template->url->href(
                'TaskViewController',
                'show',
                array(
                    'task_id' => $this->task['id'],
                    'project_id' => $this->task['project_id']
                )
            );
        }
    }

    public function assignee_name() {
        return $this->task['assignee_name'] ? $this->task['assignee_name'] : $this->task['assignee_username'];
    }

    public function assignee_initials() {
        return $this->template->user->getInitials($this->assignee_name());
    }

    public function assignee_avatar() {
        return $this->template->avatar->small(
            $this->task['owner_id'],
            $this->task['assignee_username'],
            $this->task['assignee_name'],
            $this->task['assignee_email'],
            $this->task['assignee_avatar_path'],
            'avatar-inline'
        );
    }

    public function task_footer() {
        return $this->template->render(
            'board/task_footer',
            array(
                'task' => $this->task,
                'not_editable' => $this->not_editable,
                'project' => $this->project,
            )
        );
    }
}
