const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const Admin = require('../models/Admin'); // ✅ Correct model
const auth = require('../middleware/auth');

// ✅ LOGIN ROUTE
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({ message: 'Email and password are required' });
    }

    const admin = await Admin.findOne({ email });
    if (!admin) {
      return res.status(401).json({ message: 'Invalid email or password' });
    }

    const isMatch = await bcrypt.compare(password, admin.password);
    if (!isMatch) {
      return res.status(401).json({ message: 'Invalid email or password' });
    }

    const token = jwt.sign(
      { id: admin._id, email: admin.email },
      process.env.JWT_SECRET,
      { expiresIn: '7d' }
    );

    res.json({
      admin: {
        id: admin._id,
        name: admin.name,
        email: admin.email
      },
      token
    });

  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ message: 'Server error', error: error.message });
  }
});

// Temporary route to create admin in production - REMOVE AFTER USE
router.post('/create-admin', async (req, res) => {
  try {
    const email = "admin@example.com";
    const password = "password123";
    const existing = await Admin.findOne({ email });
    if (existing) {
      return res.json({ message: 'Admin already exists' });
    }
    const hashed = await bcrypt.hash(password, 10);
    await Admin.create({ name: "Main Admin", email, password: hashed });
    res.json({ message: 'Admin created' });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// ✅ TOKEN VALIDATION ROUTE
router.get('/validate', auth, (req, res) => {
  res.json({
    valid: true,
    admin: {
      id: req.user._id,
      name: req.user.name,
      email: req.user.email,
    },
  });
});

// ✅ Export router
module.exports = router;
