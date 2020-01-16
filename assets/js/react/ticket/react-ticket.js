import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import TicketUserList from './TicketUserList';

const AssignedTicketUsers = props => {
	// TODO: getting this tickets id from react is really ugly and unreliable
	const ticketId = document.getElementById('single-ticket').dataset.ticketid; 

	const [staffUsers, setStaffUsers] = useState([])
	const [organizationUsers, setOrganizationUsers] = useState([])
	const [activeUsers, setActiveUsers] = useState([])


	// async function fetchData() {
	// 	fetch('/api/ticket/organization/users/'+ticketId)
	// 		.then((response) => {
	// 			return response.json();
	// 		})
	// 		.then((userData) => {
	// 			setOrganizationUsers(userData)
	// 			isLoadedHandler()
	// 		})

	// 	fetch('/api/ticket/staff/users/'+ticketId)
	// 		.then((response) => {
	// 			return response.json();
	// 		})
	// 		.then((userData) => {
	// 			setStaffUsers(userData)
	// 			isLoadedHandler()
	// 		})
	// 	fetch('/api/ticket/users/assigned/'+ticketId)
	// 		.then((response) => {
	// 			return response.json();
	// 		})
	// 		.then((userData) => {
	// 			setActiveUsers(userData)
	// 			isLoadedHandler()
	// 		})
	// }
	useEffect(function fetchData() {
		fetch('/api/ticket/organization/users/'+ticketId)
			.then((response) => {
				return response.json();
			})
			.then((userData) => {
				setOrganizationUsers(userData)
			})

		fetch('/api/ticket/staff/users/'+ticketId)
			.then((response) => {
				return response.json();
			})
			.then((userData) => {
				setStaffUsers(userData)
			})
		fetch('/api/ticket/users/assigned/'+ticketId)
			.then((response) => {
				return response.json();
			})
			.then((userData) => {
				setActiveUsers(userData)
			})
	}, [])

	function addUserHandler(user) {
		fetch('/api/ticket/organization/users/add/'+ticketId, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept' : 'application/json',
			},
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			credentials: 'same-origin', // include, *same-origin, omit
			referrerPolicy: 'no-referrer',
			body: JSON.stringify(user)
		})
		.then((response) => response.json())
		.then((data) => {
			console.log(data);
		})
	}

	function removeUserHandler(user) {
		fetch('/api/ticket/organization/users/remove/'+ticketId, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept' : 'application/json',
			},
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			credentials: 'same-origin', // include, *same-origin, omit
			referrerPolicy: 'no-referrer',
			body: JSON.stringify(user)
		})
		.then((response) => response.json())
		.then((data) => {
			console.log(data);
		})
	}
	
	const activeUserHandler = (user) => {
		// 	// TODO: Its not removing users or adding ?
		if (activeUsers.find(activeUser => activeUser.id === user.id)) {
			removeUserHandler(user)
			setActiveUsers(activeUsers.filter(activeUser => activeUser.id !== user.id));
		} else {
			addUserHandler(user)
			setActiveUsers([...activeUsers, user])
		}
		// fetchData();
	}


	return (
		<>
		<TicketUserList title="Assign Staff" activeUsers={activeUsers} activeUserHandler={activeUserHandler} users={staffUsers} />
		<TicketUserList title="Assign Users" activeUsers={activeUsers} activeUserHandler={activeUserHandler} users={organizationUsers} />
		</>
	)
}

ReactDOM.render(<AssignedTicketUsers />, document.getElementById('assigned-ticket-users'))