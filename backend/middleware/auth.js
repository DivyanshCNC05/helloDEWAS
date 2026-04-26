const jwt = require('jsonwebtoken');
const Admin = require('../models/Admin');

module.exports = async (req, res, next) => {
  try {
    const header = req.headers.authorization || req.headers.Authorization;
    if (!header) return res.status(401).json({ message: 'No token provided' });

    const parts = header.split(' ');
    if (parts.length !== 2 || parts[0] !== 'Bearer') {
      return res.status(401).json({ message: 'Invalid auth format' });
    }

    const token = parts[1];
    const decoded = jwt.verify(token, process.env.JWT_SECRET);

    const admin = await Admin.findById(decoded.id).select('-password');
    if (!admin) return res.status(401).json({ message: 'Admin not found' });

    req.user = admin;
    next();
  } catch (err) {
    console.error('Auth error:', err.message);
    return res.status(401).json({ message: 'Unauthorized', error: err.message });
  }
};
