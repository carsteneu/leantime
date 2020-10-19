<?php

namespace leantime\domain\controllers {

    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;

    class show
    {

        private $tpl;
        private $dashboardRepo;
        private $projectService;
        private $sprintService;
        private $ticketService;
        private $userService;
        private $timesheetService;


		/**
		 * show constructor.
		 */
		public function __construct()
        {

            $this->tpl = new core\template();
            $this->dashboardRepo = new repositories\mydashboard();
            $this->projectService = new services\projects();
            $this->sprintService = new services\sprints();
            $this->ticketService = new services\tickets();
            $this->userService = new services\users();
            $this->timesheetService = new services\timesheets();
            $this->language = new core\language();

            $_SESSION['lastPage'] = BASE_URL."/mydashboard/show";

            $reportService = new services\reports();
            $reportService->dailyIngestion();

        }

        /**
         * @return void
         */
        public function get()
        {

            $this->tpl->assign('allUsers', $this->userService->getAll());

			/**
			 * Projects of User
			 */
			$userid = $this->userService->getNumberOfUsers();
			$projects = $this->projectService->getProjectsAssignedToUser($userid);
			//print_r($projects);

			/**
			 * Projects and Progress
			 */
			$progress = array();
			foreach ($projects as $project) {
				$progress[$project['id']] = $this->projectService->getProjectProgress($project['id']);
			}

			$this->tpl->assign('projects', $projects);
            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign("currentProjectName", $this->projectService->getProjectName($_SESSION['currentProject']));


			/**
			 * Milestones from all projects, orderd by projectID
			 */

			$milestones = array();
			foreach ($projects as $project)
			{
				$milestones[$project['id']] = $this->ticketService->getAllMilestones($project['id']);
			}
            $this->tpl->assign('milestones', $milestones);

			/**
			 * Tickets
			 */

            //Search for all open tickets of the current project...
			$this->tpl->assign('tickets', $this->ticketService->getOpenUserTicketsThisWeekAndLater($_SESSION["userdata"]["id"], $_SESSION['currentProject']));

            //Search for all open tickets from all projects of current user
            $this->tpl->assign('allTickets', $this->ticketService->getOpenUserTicketsThisWeekAndLater($_SESSION["userdata"]["id"],""));
			$this->tpl->assign('allTicketsChanged', $this->ticketService->getOpenUserTicketsChanged($_SESSION["userdata"]["id"],"","changed",100));

			$this->tpl->assign("onTheClock", $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign("types", $this->ticketService->getTicketTypes());

            // rotate through projects $_SESSION['currentProject']
			$statLabels = array();
			foreach ($projects as $project)
			{
				$_SESSION['currentProject']=$project['id'];
				$statLabels[$project['id']]=$this->ticketService->getStatusLabels();
			}
			//print_r($statLabels);
			//unset($_SESSION['currentProject']);
            $this->tpl->assign("statusLabels", $statLabels);

            $this->tpl->display('mydashboard.show');

        }

		/**
		 * @param $params
		 */
		public function post($params)
        {

            if (isset($params['quickadd']) == true) {

                $result = $this->ticketService->quickAddTicket($params);

                if (isset($result["status"])) {
                    $this->tpl->setNotification($result["message"], $result["status"]);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                }

                $this->tpl->redirect(BASE_URL."/mydashboard/show");
            }


        }
    }
}
