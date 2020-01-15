import React, { useState, useEffect } from 'react';
import Button from 'react-bootstrap/Button';

const TicketUser = props => {
	const [status, setStatus] = useState(props.status)

	useEffect(() => {
		setStatus(props.status)
	}, [props.status])

	const statusHandler = (currentStatus) => {
		setStatus(currentStatus === true ? false : true)
		props.activeUserHandler(props.user)
	}

	return <Button
				onClick={() => statusHandler(status)}
				variant={status === true ? "success" : "danger"}>{props.user.email}
			</Button>
}

export default TicketUser;